<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VideoSessionController extends Controller
{
    /**
     * Check if video session can be started
     */
    public function checkVideoSession(Request $request)
    {
        try {
            $videoId = $request->input('video_id');
            $sessionId = $request->input('session_id');
            $userId = $request->input('user_id');
            
            if (!$videoId || !$sessionId || !$userId) {
                return response()->json(['success' => false, 'message' => 'Missing required parameters']);
            }
            
            $sessionKey = "video_session_{$userId}_{$videoId}";
            $existingSession = Cache::get($sessionKey);
            
            if ($existingSession && $existingSession['session_id'] !== $sessionId) {
                // Video is playing in another session
                $timeDiff = Carbon::now()->diffInSeconds(Carbon::parse($existingSession['last_heartbeat']));
                
                if ($timeDiff < 60) { // Session is active (heartbeat within 1 minute)
                    return response()->json([
                        'success' => false,
                        'message' => 'Video is already playing in another browser/device',
                        'existing_session' => true
                    ]);
                }
            }
            
            // Create new session
            Cache::put($sessionKey, [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'video_id' => $videoId,
                'started_at' => Carbon::now(),
                'last_heartbeat' => Carbon::now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ], now()->addHours(4)); // Session expires in 4 hours
            
            Log::info("Video session started", [
                'user_id' => $userId,
                'video_id' => $videoId,
                'session_id' => $sessionId,
                'ip' => $request->ip()
            ]);
            
            return response()->json(['success' => true, 'message' => 'Session started successfully']);
            
        } catch (\Exception $e) {
            Log::error('Video session check failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Session check failed'], 500);
        }
    }
    
    /**
     * Send heartbeat to maintain session
     */
    public function videoHeartbeat(Request $request)
    {
        try {
            $videoId = $request->input('video_id');
            $sessionId = $request->input('session_id');
            $userId = $request->input('user_id');
            
            $sessionKey = "video_session_{$userId}_{$videoId}";
            $session = Cache::get($sessionKey);
            
            if ($session && $session['session_id'] === $sessionId) {
                // Update heartbeat
                $session['last_heartbeat'] = Carbon::now();
                Cache::put($sessionKey, $session, now()->addHours(4));
                
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false, 'message' => 'Session not found']);
            
        } catch (\Exception $e) {
            Log::error('Video heartbeat failed: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Check for session conflicts
     */
    public function checkVideoConflict(Request $request)
    {
        try {
            $videoId = $request->input('video_id');
            $sessionId = $request->input('session_id');
            $userId = $request->input('user_id');
            
            $sessionKey = "video_session_{$userId}_{$videoId}";
            $session = Cache::get($sessionKey);
            
            if (!$session || $session['session_id'] !== $sessionId) {
                // This session is no longer active
                return response()->json([
                    'success' => false,
                    'force_stop' => true,
                    'message' => 'Video session terminated'
                ]);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Video conflict check failed: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Force play video in current session
     */
    public function forceVideoSession(Request $request)
    {
        try {
            $videoId = $request->input('video_id');
            $sessionId = $request->input('session_id');
            $userId = $request->input('user_id');
            
            $sessionKey = "video_session_{$userId}_{$videoId}";
            
            // Force create new session
            Cache::put($sessionKey, [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'video_id' => $videoId,
                'started_at' => Carbon::now(),
                'last_heartbeat' => Carbon::now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'forced' => true
            ], now()->addHours(4));
            
            Log::info("Video session forced", [
                'user_id' => $userId,
                'video_id' => $videoId,
                'session_id' => $sessionId,
                'ip' => $request->ip()
            ]);
            
            return response()->json(['success' => true, 'message' => 'Session forced successfully']);
            
        } catch (\Exception $e) {
            Log::error('Force video session failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to force session'], 500);
        }
    }
    
    /**
     * End video session
     */
    public function endVideoSession(Request $request)
    {
        try {
            $videoId = $request->input('video_id');
            $sessionId = $request->input('session_id');
            $userId = $request->input('user_id');
            
            $sessionKey = "video_session_{$userId}_{$videoId}";
            $session = Cache::get($sessionKey);
            
            if ($session && $session['session_id'] === $sessionId) {
                Cache::forget($sessionKey);
                
                Log::info("Video session ended", [
                    'user_id' => $userId,
                    'video_id' => $videoId,
                    'session_id' => $sessionId,
                    'duration' => Carbon::now()->diffInSeconds(Carbon::parse($session['started_at']))
                ]);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('End video session failed: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    
    /**
     * Add video to user's watchlist
     */
    public function addToWatchlist(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Please login first']);
            }
            
            $videoId = $request->input('video_id');
            $user = Auth::user();
            
            if (!$videoId) {
                return response()->json(['success' => false, 'message' => 'Video ID required']);
            }
            
            // Get current watchlist
            $watchlist = $user->watchlist ?? [];
            
            // Check if already in watchlist
            if (in_array($videoId, $watchlist)) {
                return response()->json(['success' => false, 'message' => 'Already in watchlist']);
            }
            
            // Add to watchlist
            $watchlist[] = $videoId;
            $user->watchlist = $watchlist;
            $user->save();
            
            return response()->json(['success' => true, 'message' => 'Added to watchlist successfully']);
            
        } catch (\Exception $e) {
            Log::error('Add to watchlist failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add to watchlist'], 500);
        }
    }
    
    /**
     * Get active video sessions for monitoring
     */
    public function getActiveSessions(Request $request)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            // This would require implementing a way to scan cache keys
            // For now, return empty array
            return response()->json(['success' => true, 'sessions' => []]);
            
        } catch (\Exception $e) {
            Log::error('Get active sessions failed: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
