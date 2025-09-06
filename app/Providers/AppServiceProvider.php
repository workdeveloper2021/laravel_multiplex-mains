<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    private function ensureUploadsSymlink(): void
    {
        $storageUploads = storage_path('app/uploads');
        $publicUploads  = public_path('uploads');

        if (!is_dir($storageUploads)) {
            @mkdir($storageUploads, 0775, true);
        }

        // Create symlink if it doesn't exist
        if (!file_exists($publicUploads)) {
            try {
                symlink($storageUploads, $publicUploads);
                Log::info('uploads symlink created', [
                    'target' => $storageUploads,
                    'link'   => $publicUploads,
                ]);
            } catch (\Throwable $e) {
                // In restricted environments symlink can fail (Windows/shared hosting).
                Log::warning('uploads symlink creation failed', ['error' => $e->getMessage()]);
            }
        }
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureUploadsSymlink();
    }
}
