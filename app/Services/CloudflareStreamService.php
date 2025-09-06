<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CloudflareStreamService
{
    /**
     * @var string|null Cloudflare Account ID
     */
    protected $accountId;

    /**
     * @var string|null Cloudflare API Token
     */
    protected $apiToken;

    /**
     * CloudflareStreamService constructor.
     */
    public function __construct()
    {
        $this->accountId = env('CLOUDFLARE_ACCOUNT_ID');
        $this->apiToken = env('CLOUDFLARE_API_TOKEN');
    }

    /**
     * Uploads a video to Cloudflare Stream with chunked upload and returns the video metadata.
     *
     * @param UploadedFile $videoFile The video file to upload
     * @param string|null $sessionId Optional session ID
     * @return array Result status with stream details or error message
     */
    public function uploadToCloudflareStreamWithProgress(UploadedFile $videoFile, ?string $sessionId = null): array
    {
        // Validate configuration first
        if (empty($this->accountId)) {
            Log::error('Cloudflare Account ID is not configured');
            return [
                'success' => false,
                'error' => 'Cloudflare Account ID is not configured. Please check CLOUDFLARE_ACCOUNT_ID in .env file.'
            ];
        }

        if (empty($this->apiToken)) {
            Log::error('Cloudflare API Token is not configured');
            return [
                'success' => false,
                'error' => 'Cloudflare API Token is not configured. Please check CLOUDFLARE_API_TOKEN in .env file.'
            ];
        }

        try {
            $filePath = $videoFile->getRealPath();
            $fileSize = $videoFile->getSize();
            $fileName = $videoFile->getClientOriginalName();

            Log::info('Starting Cloudflare upload', [
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'account_id' => substr($this->accountId, 0, 8) . '...',
                'session_id' => $sessionId
            ]);

            $endpoint = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream";

            // Step 1: Create the upload session
            $headers = [
                'Tus-Resumable: 1.0.0',
                'Upload-Length: ' . $fileSize,
                'Upload-Metadata: ' . $this->base64EncodeMetadata([
                    'name' => $fileName,
                    'filetype' => $videoFile->getMimeType(),
                ]),
                'Authorization: Bearer ' . $this->apiToken,
            ];

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Production optimizations for session creation
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
            $response = curl_exec($ch);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headersStr = substr($response, 0, $headerSize);
            curl_close($ch);

            preg_match('/Location:\s*(.*)/i', $headersStr, $matches);
            if (!isset($matches[1])) {
                return ['success' => false, 'error' => 'Failed to get upload URL from Cloudflare'];
            }

            $uploadUrl = trim($matches[1]);

            preg_match('/\/([a-f0-9]{32})$/', $uploadUrl, $streamMatches);
            $streamId = isset($streamMatches[1]) ? $streamMatches[1] : null;

            // Step 2: Upload the file in chunks with progress tracking
            $file = fopen($filePath, 'rb');
            // Optimized for 1GB/minute: Use 64MB chunks for optimal speed/reliability balance
            $chunkSize = 64 * 1024 * 1024; // 64MB chunks
            $offset = 0;
            $totalUploaded = 0;

            while (!feof($file)) {
                $chunk = fread($file, $chunkSize);
                $chunkHeaders = [
                    'Tus-Resumable: 1.0.0',
                    'Upload-Offset: ' . $offset,
                    'Content-Type: application/offset+octet-stream',
                    'Content-Length: ' . strlen($chunk),
                    'Authorization: Bearer ' . $this->apiToken,
                ];

                $ch = curl_init($uploadUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $chunkHeaders);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $chunk);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                // Production speed optimizations
                curl_setopt($ch, CURLOPT_TIMEOUT, 1200); // 20 minutes for large chunks
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
                curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
                curl_setopt($ch, CURLOPT_PIPEWAIT, 1); // HTTP/2 multiplexing
                curl_setopt($ch, CURLOPT_FORBID_REUSE, 0); // Allow connection reuse
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, 0);

                // Enable real-time progress tracking
                curl_setopt($ch, CURLOPT_NOPROGRESS, false);
                curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($sessionId, $fileSize, $totalUploaded, $offset) {
                    if ($upload_size > 0) {
                        // Calculate overall progress including previous chunks
                        $currentTotalUploaded = $totalUploaded + $uploaded;
                        $percent = round(($currentTotalUploaded / $fileSize) * 100, 2);
                        $speed = $this->calculateSpeed($currentTotalUploaded, $sessionId);
                        $eta = $this->calculateETA($currentTotalUploaded, $fileSize, $speed);

                        // Update progress cache for real-time display
                        if ($sessionId) {
                            $cacheKey = "upload_progress_{$sessionId}";
                            \Cache::put($cacheKey, [
                                'percent' => $percent,
                                'uploaded' => $currentTotalUploaded,
                                'total' => $fileSize,
                                'status' => 'uploading',
                                'message' => "Fast uploading to Cloudflare... {$percent}%",
                                'speed' => $speed,
                                'eta' => $eta,
                                'current_chunk' => intval($offset / (64 * 1024 * 1024)) + 1
                            ], now()->addMinutes(60));
                        }

                        // Optional: Echo progress for debugging
                        if (app()->environment('local')) {
                            echo "Upload Progress: {$percent}% ({$this->formatBytes($currentTotalUploaded)}/{$this->formatBytes($fileSize)}) - Speed: {$speed} MB/s\n";
                            flush();
                            ob_flush();
                        }
                    }
                });

                $res = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode !== 204) {
                    fclose($file);
                    curl_close($ch);
                    return ['success' => false, 'error' => "Upload failed with status $httpCode"];
                }

                $offset += strlen($chunk);
                $totalUploaded += strlen($chunk);

                // Progress tracking - broadcast to session if provided
                if ($sessionId) {
                    $progressPercent = round(($totalUploaded / $fileSize) * 100, 2);
                    $this->broadcastProgress($sessionId, $progressPercent, $totalUploaded, $fileSize);
                }

                curl_close($ch);
            }

            fclose($file);

            // Step 3: Get stream details from Cloudflare
            $infoUrl = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/{$streamId}";

            $ch = curl_init($infoUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $streamResponse = curl_exec($ch);
            curl_close($ch);

            $streamData = json_decode($streamResponse, true);

            Log::info('Stream info response', [
                'stream_id' => $streamId,
                'response_success' => $streamData['success'] ?? null,
                'response_data' => $streamData
            ]);

            if (!isset($streamData['success']) || !$streamData['success']) {
                $errorMsg = $streamData['errors'][0]['message'] ?? 'Upload succeeded, but failed to fetch stream info';
                Log::error('Failed to fetch stream info', [
                    'stream_id' => $streamId,
                    'response' => $streamData
                ]);

                // If stream info fetch fails, return basic success with stream_id
                // This ensures upload process continues even if stream info API fails
                Log::warning('Returning basic stream info due to API error', ['stream_id' => $streamId]);
                return [
                    'success' => true,
                    'stream_id' => $streamId,
                    'video_url' => "https://customer-{$this->accountId}.cloudflarestream.com/{$streamId}/manifest/video.m3u8",
                    'thumbnail_url' => null,
                    'warning' => 'Stream uploaded successfully but full info unavailable'
                ];
            }

            // Handle both single stream and array responses
            $streamInfo = $streamData['result'];
            if (is_array($streamInfo) && isset($streamInfo[0])) {
                $streamInfo = $streamInfo[0]; // Take first stream if array
            }

            return [
                'success' => true,
                'stream' => $streamInfo,
                'stream_id' => $streamInfo['uid'] ?? $streamId,
                'video_url' => $streamInfo['playback']['hls'] ?? null,
                'thumbnail_url' => $streamInfo['thumbnail'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('Upload to Cloudflare failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generates a downloadable link for the uploaded file in public storage.
     *
     * @param string $streamId
     * @return string|null Public URL to the stored file or null on failure
     */
    public function generateDownloadLink(string $streamId): ?string
    {
        try {
            $apiUrl = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/{$streamId}/downloads";
            \Log::info('Generating download link', ['stream_id' => $streamId, 'api_url' => $apiUrl]);
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiToken,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if (isset($responseData['success']) && $responseData['success']) {
                return $responseData['result']['default']['url'];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Download link generation failed', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Edit metadata or settings of a video on Cloudflare Stream.
     *
     * @param string $streamId
     * @param array $data
     * @return array
     */
    public function editVideo(string $streamId, array $data): array
    {
        $url = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/{$streamId}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Delete a video from Cloudflare Stream.
     *
     * @param string $streamId
     * @return array
     */
    public function deleteVideo(string $streamId): array
    {
        try {
            $url = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/{$streamId}";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); // method DELETE
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode !== 200) {
                curl_close($ch);
                return ['success' => false, 'error' => "Video deletion failed with status $httpCode"];
            }

            curl_close($ch);

            return ['success' => true, 'message' => 'Video removed from cloudfare successfully.'];
        } catch (\Exception $e) {
            Log::error('Deletion of video failed', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => "Error occured while deleting video " . $e->getMessage()];
        }
    }

    /**
     * Get video information (GET request).
     *
     * @param string $streamId
     * @return array
     */
    public function getVideoInfo(string $streamId): array
    {
        $url = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/{$streamId}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Encodes the metadata for the upload in Base64 format.
     *
     * @param array $metadata Associative array of metadata
     * @return string Encoded metadata string
     */
    private function base64EncodeMetadata(array $metadata): string
    {
        $output = [];
        foreach ($metadata as $key => $value) {
            $output[] = $key . ' ' . base64_encode($value);
        }
        return implode(',', $output);
    }

    /**
     * Waits until the Cloudflare Stream video is processed and ready to stream.
     *
     * This method polls the video status using the Cloudflare Stream API
     * until the `readyToStream` flag becomes true or the maximum number of attempts is reached.
     *
     * @param string $streamId       The unique identifier of the video on Cloudflare Stream.
     * @param int    $maxAttempts    The maximum number of polling attempts (default: 10).
     * @param int    $delaySeconds   The delay in seconds between each polling attempt (default: 10).
     *
     * @return bool  Returns true if the video is ready to stream within the given attempts, false otherwise.
     */
    public function waitForVideoReady(string $streamId, int $maxAttempts = 10, int $delaySeconds = 10): bool
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $info = $this->getVideoInfo($streamId, []);

            if (isset($info['result']['readyToStream']) && $info['result']['readyToStream']) {
                return true;
            }
            sleep($delaySeconds);
        }
        return false;
    }

    /**
     * Broadcast upload progress to frontend via WebSocket or SSE
     *
     * @param string $sessionId Session/User ID for targeting progress updates
     * @param float $progressPercent Progress percentage (0-100)
     * @param int $uploadedBytes Total bytes uploaded so far
     * @param int $totalBytes Total file size in bytes
     * @return void
     */
    private function broadcastProgress(string $sessionId, float $progressPercent, int $uploadedBytes, int $totalBytes): void
    {
        try {
            $progressData = [
                'session_id' => $sessionId,
                'progress_percent' => $progressPercent,
                'uploaded_bytes' => $uploadedBytes,
                'total_bytes' => $totalBytes,
                'uploaded_formatted' => $this->formatBytes($uploadedBytes),
                'total_formatted' => $this->formatBytes($totalBytes),
                'timestamp' => now()->toISOString()
            ];

            // Option 1: Using Laravel Broadcasting (requires Pusher/Redis)
            // broadcast(new UploadProgressEvent($progressData));

            // Option 2: Store progress in cache/database for polling
            \Cache::put("upload_progress_{$sessionId}", $progressData, 600); // 10 minutes

            // Option 3: Log for debugging
            Log::info("Upload Progress", $progressData);
        } catch (\Exception $e) {
            Log::error("Failed to broadcast progress", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $size Size in bytes
     * @return string Formatted size string
     */
    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Calculate upload speed in MB/s
     *
     * @param int $currentUploaded Current uploaded bytes
     * @param string $sessionId Session ID for tracking
     * @return float Speed in MB/s
     */
    private function calculateSpeed(int $currentUploaded, string $sessionId): float
    {
        $cacheKey = "upload_speed_{$sessionId}";
        $now = microtime(true);

        // Get previous measurement
        $previous = Cache::get($cacheKey, [
            'uploaded' => 0,
            'time' => $now
        ]);

        // Calculate speed
        $timeDiff = $now - $previous['time'];
        $bytesDiff = $currentUploaded - $previous['uploaded'];

        $speed = $timeDiff > 0 ? ($bytesDiff / $timeDiff) / (1024 * 1024) : 0; // MB/s

        // Store current measurement
        Cache::put($cacheKey, [
            'uploaded' => $currentUploaded,
            'time' => $now
        ], now()->addMinutes(60));

        return round(max(0, $speed), 2);
    }

    /**
     * Calculate estimated time remaining
     *
     * @param int $uploaded Uploaded bytes
     * @param int $total Total bytes
     * @param float $speed Speed in MB/s
     * @return int ETA in seconds
     */
    private function calculateETA(int $uploaded, int $total, float $speed): int
    {
        if ($speed <= 0 || $uploaded >= $total) {
            return 0;
        }

        $remaining = $total - $uploaded;
        $remainingMB = $remaining / (1024 * 1024);

        return (int) round($remainingMB / $speed);
    }
}
