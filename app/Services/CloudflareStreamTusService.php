<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CloudflareStreamTusService
{
    protected string $accountId;
    protected string $apiToken;
    protected Client $http;

    public function __construct(?Client $client = null)
    {
        // Prefer conventional lower-case config keys, fallback to legacy uppercase
        $this->accountId = (string) env('CLOUDFLARE_ACCOUNT_ID', config('services.cloudflare.account_id', ''));
        $this->apiToken  = (string) env('CLOUDFLARE_API_TOKEN', config('services.cloudflare.api_token', ''));

        $stack = HandlerStack::create();

        // Simple retry policy for 429/5xx
        $stack->push(Middleware::retry(
            function ($retries, $request, $response, $exception) {
                if ($retries >= 3) return false;
                if ($exception) return true; // network errors
                if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504], true)) return true;
                return false;
            },
            function ($retries) {
                return 1000 * (2 ** ($retries - 1)); // 1s, 2s, 4s
            }
        ));

        $this->http = $client ?? new Client([
            'base_uri' => 'https://api.cloudflare.com/', // ✅ ensures absolute URL
            'timeout'  => 3600, // large uploads
            'handler'  => $stack,
            'headers'  => [
                'Authorization' => "Bearer {$this->apiToken}",
                // Let Guzzle set proper multipart boundaries when needed; no default Content-Type here
            ],
            'verify'   => true, // TLS verification ON
        ]);
    }

    /* ----------------------------------------------------------------------
     | A) Direct-user (browser) uploads via TUS (pre-signed upload URL)
     |    Used by your Blade + controller -> generate-upload-url
     * ---------------------------------------------------------------------*/

    /**
     * Generate a signed TUS upload URL for direct browser → Cloudflare uploads.
     * Returns: ['success'=>bool, 'upload_url'=>string, 'stream_id'=>string, 'endpoint'=>string, 'account_id'=>string]
     */
    public function generateSignedUploadUrl(array $metadata): array
    {
        if (empty($this->accountId) || empty($this->apiToken)) {
            return ['success' => false, 'error' => 'Cloudflare credentials missing'];
        }

        $filename = (string)($metadata['name'] ?? $metadata['filename'] ?? 'upload.mp4');
        $filesize = (int)   ($metadata['size'] ?? $metadata['filesize'] ?? 0);
        $filetype = (string)($metadata['type'] ?? $metadata['filetype'] ?? 'video/*');

        $meta = (array)($metadata['meta'] ?? []);
        $requireSigned = (bool) config('services.cloudflare.require_signed_urls', true);
        $thumbnailPct  = (float) config('services.cloudflare.thumbnail_pct', 0.1);
        $maxDuration   = (int)   config('services.cloudflare.max_duration_sec', 0);

        try {
            // Cloudflare Stream: create a direct-user upload
            // POST /client/v4/accounts/{account}/stream?direct_user=true
            $res = $this->http->post("client/v4/accounts/{$this->accountId}/stream?direct_user=true", [
                'json' => array_filter([
                    'requireSignedURLs'     => $requireSigned,
                    'thumbnailTimestampPct' => $thumbnailPct,
                    'maxDurationSeconds'    => $maxDuration ?: null,
                    // extra metadata for your pipeline
                    'creator'               => (string)($meta['user_id'] ?? 'system'),
                    'meta'                  => [
                        'filename'   => $filename,
                        'filesize'   => $filesize,
                        'filetype'   => $filetype,
                        'movie_id'   => $meta['movie_id']   ?? null,
                        'channel_id' => $meta['channel_id'] ?? null,
                    ],
                ]),
                // Explicit JSON header
                'headers' => ['Content-Type' => 'application/json'],
            ]);

            $data = json_decode((string) $res->getBody(), true);
            if (!is_array($data) || empty($data['success'])) {
                $msg = $data['errors'][0]['message'] ?? 'Cloudflare create upload failed';
                return ['success' => false, 'error' => $msg];
            }

            $result = $data['result'] ?? [];
            $uploadUrl = $result['uploadURL'] ?? null;
            $uid       = $result['uid'] ?? ($result['id'] ?? null);

            if (empty($uploadUrl) || empty($uid)) {
                return ['success' => false, 'error' => 'Cloudflare response missing upload URL or uid'];
            }

            return [
                'success'    => true,
                'upload_url' => $uploadUrl,
                'stream_id'  => $uid,
                'endpoint'   => "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream",
                'account_id' => $this->accountId,
            ];
        } catch (GuzzleException $e) {
            Log::error('Cloudflare generateSignedUploadUrl exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ----------------------------------------------------------------------
     | B) Server-side direct upload (no local storage)
     |    Used by legacy two-phase endpoint you kept, but streams straight from tmp file
     * ---------------------------------------------------------------------*/

    /**
     * One-phase server-side upload (no persistence): stream PHP tmp file → Cloudflare.
     */
    public function uploadVideo(UploadedFile $videoFile, ?string $sessionId = null): array
    {
        if (empty($this->accountId) || empty($this->apiToken)) {
            return ['success' => false, 'error' => 'Cloudflare credentials missing'];
        }

        try {
            $fileName = $videoFile->getClientOriginalName();
            $mime     = $videoFile->getMimeType() ?: 'application/octet-stream';
            $size     = (int) $videoFile->getSize();
            $tmpPath  = $videoFile->getRealPath();

            if (!$tmpPath || !is_file($tmpPath)) {
                return ['success' => false, 'error' => 'Temporary upload file not found'];
            }

            Log::info('🚀 Direct upload to Cloudflare (no local storage)', [
                'file_name' => $fileName,
                'file_size' => $this->formatBytes($size),
            ]);

            // 0% -> 100% purely on Cloudflare upload progress
            $this->updateProgress($sessionId, 1, 'Starting upload to Cloudflare...', 0, $size);

            $streamInfo = $this->uploadToCloudflareDirect($tmpPath, $fileName, $mime, $size, $sessionId);
            if (!$streamInfo['success']) {
                return $streamInfo;
            }

            $this->updateProgress($sessionId, 100, 'Upload completed successfully!');
            return $streamInfo;
        } catch (\Throwable $e) {
            Log::error('Upload exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cloudflare upload via multipart (progress callback). No local persistence.
     */
    protected function uploadToCloudflareDirect(string $tmpPath, string $fileName, string $mime, int $size, ?string $sessionId): array
    {
        $url = "client/v4/accounts/{$this->accountId}/stream";

        try {
            $response = $this->http->request('POST', $url, [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($tmpPath, 'rb'),
                        'filename' => $fileName,
                        'headers'  => ['Content-Type' => $mime],
                    ],
                    // You can include these if you need to enforce at upload time:
                    // ['name' => 'requireSignedURLs', 'contents' => 'true'],
                    ['name' => 'thumbnailTimestampPct', 'contents' => '1.1'],
                ],
                // Guzzle progress: function ($downloadTotal, $downloaded, $uploadTotal, $uploaded)
                'progress' => function ($dTotal, $dLoaded, $uTotal, $uLoaded) use ($sessionId) {
                    if ($uTotal > 0) {
                        $percent = max(1, min(99, ($uLoaded / $uTotal) * 100)); // 1..99 during transfer
                        $this->updateProgress($sessionId, $percent, 'Uploading to Cloudflare...', (int)$uLoaded, (int)$uTotal);
                    }
                },
            ]);

            $this->updateProgress($sessionId, 99, 'Processing on Cloudflare...');

            $httpCode = $response->getStatusCode();
            $body     = (string) $response->getBody();
            $data     = json_decode($body, true);

            if ($httpCode !== 200 || !is_array($data) || empty($data['success'])) {
                Log::error('Cloudflare upload failed', ['http_code' => $httpCode, 'response' => $body]);
                $msg = $data['errors'][0]['message'] ?? "Upload failed with status {$httpCode}";
                return ['success' => false, 'error' => "Cloudflare API error: {$msg}"];
            }

            $stream = $data['result'] ?? [];
            Log::info('✅ Cloudflare upload successful', [
                'stream_id' => $stream['uid'] ?? null,
                'file_name' => $fileName
            ]);

            return [
                'success'       => true,
                'stream_id'     => $stream['uid'] ?? null,
                'video_url'     => $stream['playback']['hls'] ?? null,
                'thumbnail_url' => $stream['thumbnail'] ?? null,
                'stream'        => $stream,
            ];
        } catch (GuzzleException $e) {
            Log::error('Cloudflare Guzzle exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ----------------------------------------------------------------------
     | Utility APIs (info, delete, download)
     * ---------------------------------------------------------------------*/

    /** Get video info */
    public function getVideoInfo(string $streamId): array
    {
        try {
            $res = $this->http->get("client/v4/accounts/{$this->accountId}/stream/{$streamId}");
            return json_decode((string) $res->getBody(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Delete video */
    public function deleteVideo(string $streamId): array
    {
        try {
            $res = $this->http->delete("client/v4/accounts/{$this->accountId}/stream/{$streamId}");
            if ($res->getStatusCode() === 200) {
                return ['success' => true, 'message' => 'Video deleted successfully'];
            }
            return ['success' => false, 'error' => "Delete failed with status {$res->getStatusCode()}"];
        } catch (GuzzleException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Generate download link */
    public function generateDownloadLink(string $streamId): ?string
    {
        try {
            $res = $this->http->post("client/v4/accounts/{$this->accountId}/stream/{$streamId}/downloads");
            $data = json_decode((string) $res->getBody(), true);
            if (!empty($data['success'])) {
                return $data['result']['default']['url'] ?? null;
            }
            return null;
        } catch (GuzzleException $e) {
            Log::error('Download link generation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /* ----------------------------------------------------------------------
     | Progress cache + helpers (unchanged)
     * ---------------------------------------------------------------------*/

    private function updateProgress(?string $sessionId, float $percent, string $message, int $uploaded = 0, int $total = 0): void
    {
        if (!$sessionId) return;

        $progressData = [
            'percent'   => round($percent, 1),
            'message'   => $message,
            'uploaded'  => $uploaded,
            'total'     => $total,
            'status'    => $percent >= 100 ? 'completed' : 'uploading',
            'stage'     => $percent < 50 ? 'server' : ($percent < 90 ? 'cloudflare' : 'processing'),
            'timestamp' => now()->toISOString(),
        ];

        if ($uploaded > 0 && $total > 0) {
            $speed = $this->calculateSpeed($uploaded, $sessionId);
            $progressData['speed'] = $speed;       // MB/s
            $progressData['eta']   = $this->calculateETA($uploaded, $total, $speed);
        }

        Cache::put("upload_progress_{$sessionId}", $progressData, now()->addHours(1));

        Log::info('Progress update', [
            'session' => substr($sessionId, 0, 8),
            'percent' => $progressData['percent'],
            'message' => $message,
        ]);
    }

    private function calculateSpeed(int $uploaded, string $sessionId): float
    {
        $cacheKey = "upload_speed_{$sessionId}";
        $now = microtime(true);
        $previous = Cache::get($cacheKey, ['uploaded' => 0, 'time' => $now]);
        $timeDiff = max(0.0001, $now - ($previous['time'] ?? $now));
        $bytesDiff = max(0, $uploaded - ($previous['uploaded'] ?? 0));
        $speed = ($bytesDiff / $timeDiff) / (1024 * 1024); // MB/s
        Cache::put($cacheKey, ['uploaded' => $uploaded, 'time' => $now], now()->addHours(1));
        return round($speed, 2);
    }

    private function calculateETA(int $uploaded, int $total, float $speed): int
    {
        if ($speed <= 0 || $uploaded >= $total) return 0;
        $remainingMB = ($total - $uploaded) / (1024 * 1024);
        return (int) round($remainingMB / $speed);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
