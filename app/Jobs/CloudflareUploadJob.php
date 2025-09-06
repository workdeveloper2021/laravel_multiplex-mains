<?php

namespace App\Jobs;

use App\Models\Movie;
use App\Services\CloudflareStreamService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use MongoDB\BSON\ObjectId;

class CloudflareUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $movieId;
    protected $localFilePath;
    protected $sessionId;
    
    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;
    
    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 7200; // 2 hours for 2GB files

    /**
     * Create a new job instance.
     */
    public function __construct($movieId, $localFilePath, $sessionId)
    {
        // Ensure movieId is string for MongoDB compatibility
        $this->movieId = is_object($movieId) ? (string) $movieId : $movieId;
        $this->localFilePath = $localFilePath;
        $this->sessionId = $sessionId;
        
        // Set queue connection to use MongoDB
        $this->connection = 'mongodb';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting background Cloudflare upload', [
                'movie_id' => $this->movieId,
                'local_file' => $this->localFilePath,
                'session_id' => $this->sessionId
            ]);

            // Handle MongoDB ObjectId properly
            if (strlen($this->movieId) === 24) {
                $movie = Movie::where('_id', new ObjectId($this->movieId))->firstOrFail();
            } else {
                $movie = Movie::findOrFail($this->movieId);
            }
            $cacheKey = "upload_progress_{$this->sessionId}";
            
            // Get full file path first
            $fullPath = Storage::disk('public')->path($this->localFilePath);
            
            // Update progress - Cloudflare stage starting (50% to 75%)
            Cache::put($cacheKey, [
                'percent' => 55,
                'uploaded' => intval(filesize($fullPath) * 0.55),
                'total' => filesize($fullPath),
                'status' => 'uploading',
                'message' => 'Starting Cloudflare upload (background)...',
                'stage' => 'cloudflare',
                'speed' => 10,
                'eta' => 120,
                'current_chunk' => 1
            ], now()->addMinutes(60));
            
            Log::info('Checking local file for background upload', [
                'local_path' => $this->localFilePath,
                'full_path' => $fullPath,
                'exists' => file_exists($fullPath),
                'movie_id' => $this->movieId
            ]);
            
            if (!file_exists($fullPath)) {
                Log::error('Local file not found for background upload', [
                    'path' => $fullPath,
                    'movie_id' => $this->movieId
                ]);
                return;
            }

            // Create UploadedFile object from local file
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $fullPath,
                basename($this->localFilePath),
                mime_content_type($fullPath),
                null,
                true // Test mode - don't check if uploaded via HTTP
            );

            // Upload to Cloudflare
            $cloudflareService = app(CloudflareStreamService::class);
            
            // Update progress - Cloudflare upload stage (50% to 90%)
            Cache::put($cacheKey, [
                'percent' => 75,
                'uploaded' => intval(filesize($fullPath) * 0.75),
                'total' => filesize($fullPath),
                'status' => 'uploading',
                'message' => 'Uploading to Cloudflare Stream (background)...',
                'stage' => 'cloudflare',
                'speed' => 25,
                'eta' => 60,
                'current_chunk' => 1
            ], now()->addMinutes(60));

            $uploadResult = $cloudflareService->uploadToCloudflareStreamWithProgress(
                $uploadedFile,
                $this->sessionId
            );

            if ($uploadResult['success']) {
                Log::info('✅ Cloudflare upload SUCCESS - Processing response', [
                    'movie_id' => $this->movieId,
                    'stream_id' => $uploadResult['stream_id']
                ]);
                
                $videoStreamId = $uploadResult['stream_id'];
                $video_url = $uploadResult['video_url'];
                $thumbnail = $uploadResult['thumbnail_url'];
                
                // Generate download link if enabled
                $downloadUrl = null;
                if ($movie->enable_download === '1' || $movie->enable_download === true) {
                    if ($cloudflareService->waitForVideoReady($videoStreamId)) {
                        $downloadUrl = $cloudflareService->generateDownloadLink($videoStreamId);
                    }
                }
                
                // Update movie with Cloudflare details
                $updateData = [
                    'video_url' => $video_url,
                    'videoContent_id' => $videoStreamId,
                    'stream_id' => $videoStreamId,
                    'download_url' => $downloadUrl,
                    'status' => 'pending' // Ready for admin review
                ];
                
                // Only update thumbnail if movie doesn't have one
                if (empty($movie->thumbnail_url) && !empty($thumbnail)) {
                    $updateData['thumbnail_url'] = $thumbnail;
                }
                
                $movie->update($updateData);
                
                Log::info('📝 Movie updated with Cloudflare details', [
                    'movie_id' => $this->movieId,
                    'video_url' => $video_url
                ]);

                // 🗑️ IMMEDIATE FILE DELETION after Cloudflare success response
                if (Storage::disk('public')->exists($this->localFilePath)) {
                    $fileSize = Storage::disk('public')->size($this->localFilePath);
                    Storage::disk('public')->delete($this->localFilePath);
                    
                    Log::info('🗑️ IMMEDIATE DELETION: Local file removed after Cloudflare success!', [
                        'movie_id' => $this->movieId,
                        'deleted_file' => $this->localFilePath,
                        'file_size_deleted' => round($fileSize / (1024*1024), 2) . ' MB',
                        'cloudflare_stream_id' => $videoStreamId
                    ]);
                } else {
                    Log::warning('Local file not found for deletion', [
                        'path' => $this->localFilePath,
                        'movie_id' => $this->movieId
                    ]);
                }
                
                // Update progress to completed (100%)
                Cache::put($cacheKey, [
                    'percent' => 100,
                    'uploaded' => filesize($fullPath),
                    'total' => filesize($fullPath),
                    'status' => 'completed',
                    'message' => 'Both stages completed! Server + Cloudflare done. File auto-deleted.',
                    'stage' => 'completed',
                    'speed' => 0,
                    'eta' => 0,
                    'current_chunk' => 0
                ], now()->addMinutes(5));

                Log::info('Background Cloudflare upload completed', [
                    'movie_id' => $this->movieId,
                    'stream_id' => $videoStreamId,
                    'local_file_deleted' => true
                ]);

            } else {
                // Cloudflare upload failed, keep local file and log error
                Log::error('Background Cloudflare upload failed', [
                    'movie_id' => $this->movieId,
                    'error' => $uploadResult['error'] ?? 'Unknown error',
                    'local_file_preserved' => $this->localFilePath
                ]);
                
                // Update progress with error
                Cache::put($cacheKey, [
                    'percent' => 0,
                    'uploaded' => 0,
                    'total' => 0,
                    'status' => 'error',
                    'message' => 'Background upload failed. File saved locally.',
                    'speed' => 0,
                    'eta' => 0,
                    'current_chunk' => 0
                ], now()->addMinutes(5));
            }

        } catch (\Exception $e) {
            Log::error('CloudflareUploadJob failed', [
                'movie_id' => $this->movieId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update progress with error
            Cache::put("upload_progress_{$this->sessionId}", [
                'percent' => 0,
                'uploaded' => 0,
                'total' => 0,
                'status' => 'error',
                'message' => 'Background processing failed: ' . $e->getMessage(),
                'speed' => 0,
                'eta' => 0,
                'current_chunk' => 0
            ], now()->addMinutes(5));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CloudflareUploadJob failed permanently', [
            'movie_id' => $this->movieId,
            'local_file' => $this->localFilePath,
            'error' => $exception->getMessage()
        ]);
    }
}
