<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CloudflareStreamServiceSimple
{
    protected string $accountId;
    protected string $apiToken;
    protected Client $http;

    public function __construct(?Client $client = null)
    {
        $this->accountId = trim((string) config('services.cloudflare.account_id', ''));
        $this->apiToken  = trim((string) config('services.cloudflare.api_token', ''));

        if ($this->accountId === '' || $this->apiToken === '') {
            Log::error('Cloudflare credentials missing or empty', [
                'account_id_present' => $this->accountId !== '',
                'token_len'          => strlen($this->apiToken),
            ]);
        }

        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            function ($retries, $request, $response, $exception) {
                if ($retries >= 3) return false;
                if ($exception) return true;
                if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504], true)) return true;
                return false;
            },
            fn($retries) => 1000 * (2 ** ($retries - 1))
        ));

        $this->http = $client ?? new Client([
            'base_uri' => 'https://api.cloudflare.com/',
            'timeout'  => 3600,
            'handler'  => $stack,
            'verify'   => true,
        ]);
    }

    /** ---------- Helpers: centralize auth + request ---------- */

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Accept'        => 'application/json',
        ];
    }

    private function cfUrl(string $path): string
    {
        // Always full URL to avoid base_uri edge cases
        return "https://api.cloudflare.com/client/v4{$path}";
    }

    private function cfRequest(string $method, string $path, array $options = [])
    {
        if ($this->accountId === '' || $this->apiToken === '') {
            throw new \RuntimeException('Cloudflare credentials missing (account/token)');
        }

        // Merge headers so per-call headers can extend (not overwrite) auth
        $options['headers'] = array_merge($this->authHeaders(), $options['headers'] ?? []);

        return $this->http->request($method, $this->cfUrl($path), $options);
    }

    /** ---------- Public API ---------- */

    public function uploadVideo(UploadedFile $videoFile, ?string $sessionId = null): array
    {
        if (empty($this->accountId) || empty($this->apiToken)) {
            return ['success' => false, 'error' => 'Cloudflare credentials missing'];
        }

        try {
            $fileName = $videoFile->getClientOriginalName();
            $mime     = $videoFile->getMimeType() ?: 'application/octet-stream';
            $size     = $videoFile->getSize();

            Log::info('🚀 Direct upload to Cloudflare (no local storage)', [
                'file_name' => $fileName,
                'file_size' => $this->formatBytes($size),
            ]);

            $tmpPath = $videoFile->getRealPath();
            if (!$tmpPath || !is_file($tmpPath)) {
                return ['success' => false, 'error' => 'Temporary upload file not found'];
            }

            $this->updateProgress($sessionId, 1, 'Starting direct upload to Cloudflare...');

            $streamInfo = $this->uploadToCloudflare($tmpPath, $fileName, $mime, $size, $sessionId);
            if (!$streamInfo['success']) {
                return $streamInfo;
            }

            $this->updateProgress($sessionId, 100, 'Upload completed successfully!');

            // Removed undefined $destPath cleanup
            return $streamInfo;
        } catch (\Throwable $e) {
            Log::error('Upload exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getVideoInfo(string $streamId): array
    {
        try {
            $res  = $this->cfRequest('GET', "/accounts/{$this->accountId}/stream/{$streamId}");
            $data = json_decode((string) $res->getBody(), true) ?? [];
            return $data;
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteVideo(string $streamId): array
    {
        try {
            $res = $this->cfRequest('DELETE', "/accounts/{$this->accountId}/stream/{$streamId}");
            if ($res->getStatusCode() === 200) {
                return ['success' => true, 'message' => 'Video deleted successfully'];
            }
            return ['success' => false, 'error' => "Delete failed with status {$res->getStatusCode()}"];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateDownloadLink(string $streamId): ?string
    {
        try {
            // If you need to include body options, add `'json' => [...]` to options.
            $res  = $this->cfRequest('POST', "/accounts/{$this->accountId}/stream/{$streamId}/downloads");
            $data = json_decode((string) $res->getBody(), true);
            if (!empty($data['success'])) {
                return $data['result']['default']['url'] ?? null;
            }
            return null;
        } catch (\Throwable $e) {
            Log::error('Download link generation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /** ---------- Internal: Upload ---------- */

    protected function uploadToCloudflare(string $path, string $fileName, string $mime, int $size, ?string $sessionId): array
    {
        if ($this->accountId === '' || $this->apiToken === '') {
            return ['success' => false, 'error' => 'Cloudflare credentials missing (account/token)'];
        }

        if (strlen($this->apiToken) < 20) {
            Log::error('Cloudflare API token looks invalid/short', ['len' => strlen($this->apiToken)]);
            return ['success' => false, 'error' => 'Cloudflare API token invalid/short'];
        }

        Log::info('Cloudflare upload init', [
            'account_id' => $this->accountId,
            'file_name'  => $fileName,
            'file_size'  => $this->formatBytes($size),
        ]);

        try {
            $response = $this->cfRequest('POST', "/accounts/{$this->accountId}/stream", [
                // Do NOT set global Content-Type for multipart; Guzzle will set boundary.
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($path, 'rb'),
                        'filename' => $fileName,
                        'headers'  => ['Content-Type' => $mime],
                    ],
                ],
                'progress' => function ($dTotal, $dLoaded, $uTotal, $uLoaded) use ($sessionId) {
                    if ($uTotal > 0) {
                        $phase = 50 + min(50, ($uLoaded / $uTotal) * 50);
                        $this->updateProgress($sessionId, $phase, 'Uploading to Cloudflare...', (int)$uLoaded, (int)$uTotal);
                    }
                },
                'expect' => false, // avoid 100-continue quirks
            ]);

            $this->updateProgress($sessionId, 95, 'Processing on Cloudflare...');

            $httpCode = $response->getStatusCode();
            $data     = json_decode((string) $response->getBody(), true);

            if ($httpCode !== 200 || empty($data['success'])) {
                Log::error('Cloudflare upload failed', ['http_code' => $httpCode, 'response' => $data]);
                $msg = $data['errors'][0]['message'] ?? "Upload failed with status {$httpCode}";
                return ['success' => false, 'error' => "Cloudflare API error: {$msg}"];
            }

            $stream = $data['result'] ?? [];
            Log::info('Cloudflare upload successful', ['stream_id' => $stream['uid'] ?? null]);

            return [
                'success'       => true,
                'stream_id'     => $stream['uid'] ?? null,
                'video_url'     => $stream['playback']['hls'] ?? null,
                'thumbnail_url' => $stream['thumbnail'] ?? null,
                'stream'        => $stream,
            ];
        } catch (\Throwable $e) {
            Log::error('Cloudflare Guzzle exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** ---------- Progress + utils (unchanged) ---------- */

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
            $progressData['speed'] = $speed;
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
        $speed = ($bytesDiff / $timeDiff) / (1024 * 1024);
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
