<?php

namespace App\Http\Controllers;

use App\Jobs\BuildAndDispatchFcmMessages;
use App\Repositories\DeviceTokenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    private DeviceTokenRepository $tokenRepository;

    public function __construct(DeviceTokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Send manual push notification (Admin only)
     * POST /notifications/manual
     */
    public function sendManual(Request $request)
    {
        // Check if user is admin
        $user = Auth::user();
        if (!$user || ($user->role !== 'admin' && !$user->is_super_admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'image' => 'nullable|string|url',
            'target' => 'required|string|in:all,channel,users',
            'channel_id' => 'required_if:target,channel|string|nullable',
            'user_ids' => 'required_if:target,users|array|nullable',
            'user_ids.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $tokens = [];

        try {
            // Get tokens based on target type
            switch ($data['target']) {
                case 'all':
                    $tokens = $this->tokenRepository->getAllTokens();
                    break;
                    
                case 'channel':
                    $tokens = $this->tokenRepository->getTokensForChannel($data['channel_id']);
                    break;
                    
                case 'users':
                    $tokens = $this->tokenRepository->getTokensForUsers($data['user_ids']);
                    break;
            }

            if (empty($tokens)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid FCM tokens found for the specified target.'
                ], 404);
            }

            // Dispatch notification job
            BuildAndDispatchFcmMessages::dispatch(
                title: $data['title'],
                body: $data['body'],
                tokens: $tokens,
                image: $data['image'] ?? null,
                notificationType: 'manual'
            );

            Log::info('Manual FCM notification dispatched', [
                'admin_id' => $user->_id,
                'admin_name' => $user->name,
                'target' => $data['target'],
                'title' => $data['title'],
                'token_count' => count($tokens)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification queued successfully.',
                'data' => [
                    'target_count' => count($tokens),
                    'target' => $data['target']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Manual FCM notification failed', [
                'admin_id' => $user->_id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification. Please try again.'
            ], 500);
        }
    }

    /**
     * Update user's FCM token
     */
    public function updateToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        try {
            $success = $this->tokenRepository->updateUserToken(
                $user->_id, 
                $request->fcm_token
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'FCM token updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update FCM token'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('FCM token update failed', [
                'user_id' => $user->_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
