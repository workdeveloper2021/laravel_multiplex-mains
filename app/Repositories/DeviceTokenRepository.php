<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\ChannelSubs;
use Illuminate\Support\Facades\Log;

class DeviceTokenRepository
{
    /**
     * Get all valid FCM tokens for all users
     */
    public function getAllTokens(): array
    {
        return User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->filter() // Remove null/empty values
            ->values()
            ->toArray();
    }

    /**
     * Get FCM tokens for specific users
     */
    public function getTokensForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        return User::whereIn('_id', $userIds)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get FCM tokens for channel subscribers
     */
    public function getTokensForChannel(string $channelId): array
    {
        // Get subscriber user IDs for the channel
        $subscriberIds = ChannelSubs::where('channel', $channelId)
            ->pluck('user')
            ->unique()
            ->toArray();

        Log::info('Channel subscribers found', [
            'channel_id' => $channelId,
            'subscriber_count' => count($subscriberIds)
        ]);

        if (empty($subscriberIds)) {
            return [];
        }

        // Get FCM tokens for those subscribers
        $tokens = User::whereIn('_id', $subscriberIds)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        Log::info('Valid FCM tokens found for channel', [
            'channel_id' => $channelId,
            'token_count' => count($tokens)
        ]);

        return $tokens;
    }

    /**
     * Update user's FCM token
     */
    public function updateUserToken(string $userId, ?string $fcmToken): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            $user->fcm_token = $fcmToken;
            return $user->save();
        } catch (\Exception $e) {
            Log::error('Failed to update FCM token', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove invalid tokens
     */
    public function removeInvalidTokens(array $invalidTokens): int
    {
        if (empty($invalidTokens)) {
            return 0;
        }

        try {
            $updated = User::whereIn('fcm_token', $invalidTokens)
                ->update(['fcm_token' => null]);

            Log::info('Removed invalid FCM tokens', [
                'count' => $updated,
                'tokens' => $invalidTokens
            ]);

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to remove invalid tokens', [
                'error' => $e->getMessage(),
                'tokens' => $invalidTokens
            ]);
            return 0;
        }
    }
}
