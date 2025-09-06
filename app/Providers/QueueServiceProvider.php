<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Auto-start queue worker in production
        if (app()->environment('production')) {
            $this->ensureQueueWorkerRunning();
        }
    }

    /**
     * Ensure queue worker is running for background video uploads
     */
    private function ensureQueueWorkerRunning(): void
    {
        try {
            $pidFile = storage_path('app/queue-worker.pid');
            
            // Check if queue worker is already running
            if (file_exists($pidFile)) {
                $pid = file_get_contents($pidFile);
                
                // Check if process is actually running
                $isRunning = exec("ps -p $pid") != '';
                
                if ($isRunning) {
                    Log::info('Queue worker already running', ['pid' => $pid]);
                    return;
                }
            }
            
            // Start queue worker in background
            $command = 'php ' . base_path('artisan') . ' queue:work --queue=uploads,default --timeout=3600 --memory=512 --tries=3 --delay=5';
            $pid = exec("nohup $command > " . storage_path('logs/queue.log') . " 2>&1 & echo $!");
            
            // Save PID for monitoring
            file_put_contents($pidFile, $pid);
            
            Log::info('Queue worker started automatically', [
                'pid' => $pid,
                'command' => $command
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to auto-start queue worker', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
