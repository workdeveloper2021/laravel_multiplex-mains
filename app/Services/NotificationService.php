<?php

namespace App\Services;

use App\Jobs\BuildAndDispatchFcmMessages;
use App\Repositories\DeviceTokenRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private DeviceTokenRepository $tokenRepository;

    public function __construct(DeviceTokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Send notification for admin upload (all users)
     */
    public function notifyAdminUpload(string $contentType, string $title, ?string $thumbnailUrl = null): void
    {
        $tokens = $this->tokenRepository->getAllTokens();
        
        if (empty($tokens)) {
            Log::info('Admin upload notification skipped - no valid tokens', [
                'content_type' => $contentType,
                'title' => $title
            ]);
            return;
        }

        BuildAndDispatchFcmMessages::dispatch(
            title: "New {$contentType} added",
            body: "{$title} is now available.",
            tokens: $tokens,
            image: $thumbnailUrl,
            notificationType: 'admin_upload'
        );

        Log::info('Admin upload notification dispatched', [
            'content_type' => $contentType,
            'title' => $title,
            'token_count' => count($tokens)
        ]);
    }

    /**
     * Send notification for channel upload (subscribers only)
     */
    public function notifyChannelUpload(string $channelId, string $contentType, string $title, ?string $thumbnailUrl = null): void
    {
        $tokens = $this->tokenRepository->getTokensForChannel($channelId);
        
        if (empty($tokens)) {
            Log::info('Channel upload notification skipped - no valid tokens', [
                'channel_id' => $channelId,
                'content_type' => $contentType,
                'title' => $title
            ]);
            return;
        }

        BuildAndDispatchFcmMessages::dispatch(
            title: "New {$contentType} added",
            body: "{$title} is now available.",
            tokens: $tokens,
            image: $thumbnailUrl,
            notificationType: 'channel_upload'
        );

        Log::info('Channel upload notification dispatched', [
            'channel_id' => $channelId,
            'content_type' => $contentType,
            'title' => $title,
            'token_count' => count($tokens)
        ]);
    }

    /**
     * Determine if user is admin and send appropriate notifications
     */
    public function handleContentUpload(string $channelId, string $contentType, string $title, ?string $thumbnailUrl = null): void
    {
        $user = Auth::user();
        
        if (!$user) {
            Log::warning('Content upload notification skipped - no authenticated user');
            return;
        }

        // Check if user is admin/super admin
        if ($user->role === 'admin' || $user->is_super_admin) {
            $this->notifyAdminUpload($contentType, $title, $thumbnailUrl);
        } else {
            // Channel user upload - notify subscribers
            $this->notifyChannelUpload($channelId, $contentType, $title, $thumbnailUrl);
        }
    }
}
