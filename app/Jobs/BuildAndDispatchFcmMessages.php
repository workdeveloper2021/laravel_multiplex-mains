<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BuildAndDispatchFcmMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 300]; // Retry after 1, 2, 5 minutes

    private string $title;
    private string $body;
    private ?string $image;
    private array $tokens;
    private string $notificationType;

    /**
     * Create a new job instance
     */
    public function __construct(
        string $title, 
        string $body, 
        array $tokens, 
        ?string $image = null,
        string $notificationType = 'manual'
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->image = $image;
        $this->tokens = array_filter($tokens); // Remove empty tokens
        $this->notificationType = $notificationType;
    }

    /**
     * Execute the job
     */
    public function handle(FcmService $fcmService): void
    {
        if (empty($this->tokens)) {
            Log::info('FCM job skipped - no valid tokens', [
                'type' => $this->notificationType,
                'title' => $this->title
            ]);
            return;
        }

        Log::info('FCM notification job started', [
            'type' => $this->notificationType,
            'title' => $this->title,
            'token_count' => count($this->tokens)
        ]);

        try {
            $result = $fcmService->sendToTokens(
                $this->tokens,
                $this->title,
                $this->body,
                $this->image
            );

            Log::info('FCM notification completed', [
                'type' => $this->notificationType,
                'title' => $this->title,
                'success' => $result['success'],
                'failure' => $result['failure'],
                'total_tokens' => count($this->tokens)
            ]);

            // Log errors if any
            if (!empty($result['errors'])) {
                Log::warning('FCM notification errors occurred', [
                    'type' => $this->notificationType,
                    'errors' => array_slice($result['errors'], 0, 10) // Log first 10 errors only
                ]);
            }

        } catch (\Exception $e) {
            Log::error('FCM notification job failed', [
                'type' => $this->notificationType,
                'title' => $this->title,
                'token_count' => count($this->tokens),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('FCM notification job failed permanently', [
            'type' => $this->notificationType,
            'title' => $this->title,
            'token_count' => count($this->tokens),
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);
    }
}
