<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecureVideoController extends Controller
{
    /**
     * Serve secure video with token authentication
     */
    public function serveSecureVideo(Request $request)
    {
        try {
            $videoUrl = $request->query('url');
            $token = $request->query('token');
            
            // Validate token
            if (!$this->validateVideoToken($token)) {
                return response()->json(['error' => 'Invalid token'], 403);
            }
            
            // Validate video URL
            if (!$this->validateVideoUrl($videoUrl)) {
                return response()->json(['error' => 'Invalid video URL'], 400);
            }
            
            // Generate time-limited signed URL
            $signedUrl = $this->generateSignedVideoUrl($videoUrl);
            
            return response()->json([
                'video_url' => $signedUrl,
                'expires_in' => 3600 // 1 hour
            ]);
            
        } catch (\Exception $e) {
            Log::error('Secure video error: ' . $e->getMessage());
            return response()->json(['error' => 'Video service unavailable'], 500);
        }
    }
    
    /**
     * Proxy video stream with additional security
     */
    public function proxyVideoStream(Request $request)
    {
        try {
            $videoUrl = $request->query('url');
            $signature = $request->query('signature');
            $expires = $request->query('expires');
            
            // Validate signature and expiry
            if (!$this->validateSignature($videoUrl, $signature, $expires)) {
                abort(403, 'Access Denied');
            }
            
            // Check if URL has expired
            if (time() > $expires) {
                abort(403, 'URL Expired');
            }
            
            // Stream the video
            return $this->streamVideo($videoUrl, $request);
            
        } catch (\Exception $e) {
            Log::error('Video streaming error: ' . $e->getMessage());
            abort(500, 'Streaming service error');
        }
    }
    
    /**
     * Get video manifest with security headers
     */
    public function getVideoManifest(Request $request)
    {
        try {
            $videoUrl = $request->query('url');
            $token = $request->query('token');
            
            if (!$this->validateVideoToken($token)) {
                abort(403, 'Access Denied');
            }
            
            // Fetch original manifest
            $response = Http::timeout(30)->get($videoUrl);
            
            if (!$response->successful()) {
                abort(404, 'Video not found');
            }
            
            $manifest = $response->body();
            
            // Modify manifest to use proxy URLs
            $secureManifest = $this->secureManifestUrls($manifest, $videoUrl);
            
            return response($secureManifest, 200, [
                'Content-Type' => 'application/x-mpegURL',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'Referrer-Policy' => 'no-referrer'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Manifest error: ' . $e->getMessage());
            abort(500, 'Manifest service error');
        }
    }
    
    /**
     * Validate video access token
     */
    private function validateVideoToken($token)
    {
        if (!$token) {
            return false;
        }
        
        // Simple validation - in production use proper JWT validation
        $decodedToken = base64_decode($token);
        
        // Check if token is not older than 1 hour
        $tokenData = json_decode($decodedToken, true);
        if (!$tokenData || !isset($tokenData['timestamp'])) {
            return false;
        }
        
        return (time() - $tokenData['timestamp']) < 3600;
    }
    
    /**
     * Validate video URL format
     */
    private function validateVideoUrl($url)
    {
        if (!$url) {
            return false;
        }
        
        // Check if it's a valid URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check if it's an allowed video format
        $allowedFormats = ['.m3u8', '.mpd', '.mp4'];
        $hasValidFormat = false;
        
        foreach ($allowedFormats as $format) {
            if (strpos($url, $format) !== false) {
                $hasValidFormat = true;
                break;
            }
        }
        
        return $hasValidFormat;
    }
    
    /**
     * Generate signed URL with expiry
     */
    private function generateSignedVideoUrl($videoUrl)
    {
        $expires = time() + 3600; // 1 hour from now
        $signature = hash_hmac('sha256', $videoUrl . $expires, env('APP_KEY'));
        
        return route('video.proxy') . '?' . http_build_query([
            'url' => $videoUrl,
            'signature' => $signature,
            'expires' => $expires
        ]);
    }
    
    /**
     * Validate URL signature
     */
    private function validateSignature($url, $signature, $expires)
    {
        $expectedSignature = hash_hmac('sha256', $url . $expires, env('APP_KEY'));
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Stream video content
     */
    private function streamVideo($videoUrl, Request $request)
    {
        // Get user's IP and user agent for logging
        $clientIp = $request->ip();
        $userAgent = $request->userAgent();
        
        Log::info("Video access from IP: {$clientIp}, User Agent: {$userAgent}");
        
        // Stream the video using Laravel's HTTP client
        $response = Http::timeout(60)->get($videoUrl);
        
        if (!$response->successful()) {
            abort(404, 'Video not found');
        }
        
        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type') ?? 'video/mp4',
            'Content-Length' => $response->header('Content-Length'),
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY'
        ]);
    }
    
    /**
     * Secure manifest URLs by replacing them with proxy URLs
     */
    private function secureManifestUrls($manifest, $baseUrl)
    {
        $lines = explode("\n", $manifest);
        $secureLines = [];
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // If line is a URL (not starting with # and not empty)
            if (!empty($trimmedLine) && !str_starts_with($trimmedLine, '#')) {
                // Convert relative URLs to absolute
                if (!filter_var($trimmedLine, FILTER_VALIDATE_URL)) {
                    $trimmedLine = $this->resolveRelativeUrl($baseUrl, $trimmedLine);
                }
                
                // Create proxy URL
                $expires = time() + 3600;
                $signature = hash_hmac('sha256', $trimmedLine . $expires, env('APP_KEY'));
                
                $proxyUrl = route('video.proxy') . '?' . http_build_query([
                    'url' => $trimmedLine,
                    'signature' => $signature,
                    'expires' => $expires
                ]);
                
                $secureLines[] = $proxyUrl;
            } else {
                $secureLines[] = $line;
            }
        }
        
        return implode("\n", $secureLines);
    }
    
    /**
     * Resolve relative URL against base URL
     */
    private function resolveRelativeUrl($baseUrl, $relativeUrl)
    {
        $base = parse_url($baseUrl);
        
        if (str_starts_with($relativeUrl, '/')) {
            return $base['scheme'] . '://' . $base['host'] . $relativeUrl;
        }
        
        $basePath = dirname($base['path'] ?? '/');
        return $base['scheme'] . '://' . $base['host'] . $basePath . '/' . $relativeUrl;
    }
}
