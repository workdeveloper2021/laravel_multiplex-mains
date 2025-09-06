<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CheckSystemStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:status';

    /**
     * The console command description.
     */
    protected $description = 'Check system status for video upload and queue processing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎬 Multiplex Video Upload System Status Check');
        $this->line('================================================');
        
        // Check storage permissions
        $this->checkStoragePermissions();
        
        // Check queue system
        $this->checkQueueSystem();
        
        // Check Cloudflare configuration
        $this->checkCloudflareConfig();
        
        // Check temporary files
        $this->checkTempFiles();
        
        $this->line('================================================');
        $this->info('System status check complete!');
        
        return 0;
    }
    
    private function checkStoragePermissions()
    {
        $this->line('📁 Storage Permissions:');
        
        $paths = [
            'movies/temp' => 'Temporary video storage',
            'movies/videos' => 'Video storage', 
            'banners' => 'Thumbnail/poster storage'
        ];
        
        foreach ($paths as $path => $description) {
            try {
                $fullPath = storage_path("app/public/{$path}");
                
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                    $this->info("  ✅ Created: {$description} ({$path})");
                } else {
                    $this->info("  ✅ Exists: {$description} ({$path})");
                }
                
                if (is_writable($fullPath)) {
                    $this->info("  ✅ Writable: {$path}");
                } else {
                    $this->error("  ❌ Not writable: {$path}");
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error with {$path}: " . $e->getMessage());
            }
        }
        $this->line('');
    }
    
    private function checkQueueSystem()
    {
        $this->line('⚡ Queue System:');
        
        // Check if queue worker is running
        $pidFile = storage_path('app/queue-worker.pid');
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            $isRunning = exec("ps -p $pid") != '';
            
            if ($isRunning) {
                $this->info("  ✅ Queue worker running (PID: {$pid})");
            } else {
                $this->warn("  ⚠️  Queue worker PID file exists but process not running");
                unlink($pidFile);
            }
        } else {
            $this->warn("  ⚠️  Queue worker not running");
            $this->comment("  💡 Run: ./auto-start-queue.sh start");
        }
        
        // Check queue driver
        $queueDriver = config('queue.default');
        $this->info("  📋 Queue driver: {$queueDriver}");
        
        $this->line('');
    }
    
    private function checkCloudflareConfig()
    {
        $this->line('☁️  Cloudflare Configuration:');
        
        $accountId = config('services.cloudflare.CLOUDFLARE_ACCOUNT_ID');
        $apiToken = config('services.cloudflare.CLOUDFLARE_API_TOKEN');
        
        if (!empty($accountId)) {
            $this->info("  ✅ Account ID configured");
        } else {
            $this->error("  ❌ Account ID missing (CLOUDFLARE_ACCOUNT_ID)");
        }
        
        if (!empty($apiToken)) {
            $this->info("  ✅ API Token configured");
        } else {
            $this->error("  ❌ API Token missing (CLOUDFLARE_API_TOKEN)");
        }
        
        $this->line('');
    }
    
    private function checkTempFiles()
    {
        $this->line('🗂️  Temporary Files:');
        
        $tempPath = storage_path('app/public/movies/temp');
        if (file_exists($tempPath)) {
            $files = glob($tempPath . '/*');
            $count = count($files);
            $totalSize = 0;
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                }
            }
            
            $this->info("  📊 Temporary files: {$count}");
            $this->info("  💾 Total size: " . $this->formatBytes($totalSize));
            
            if ($count > 10) {
                $this->warn("  ⚠️  Many temp files - check background job processing");
            }
        } else {
            $this->info("  ✅ No temporary files (clean)");
        }
        
        $this->line('');
    }
    
    private function formatBytes($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
