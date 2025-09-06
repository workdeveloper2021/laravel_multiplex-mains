<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class UploadProgressController extends Controller
{
    /**
     * Get upload progress for a session
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProgress(Request $request): JsonResponse
    {
        $sessionId = $request->get('session_id');
        
        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'error' => 'Session ID is required'
            ], 400);
        }

        $progress = Cache::get("upload_progress_{$sessionId}");

        if (!$progress) {
            return response()->json([
                'success' => false,
                'progress_percent' => 0,
                'message' => 'No active upload found'
            ]);
        }

        return response()->json([
            'success' => true,
            'progress_percent' => $progress['progress_percent'],
            'uploaded_bytes' => $progress['uploaded_bytes'],
            'total_bytes' => $progress['total_bytes'],
            'uploaded_formatted' => $progress['uploaded_formatted'],
            'total_formatted' => $progress['total_formatted'],
            'timestamp' => $progress['timestamp']
        ]);
    }

    /**
     * Server-Sent Events endpoint for real-time progress
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function streamProgress(Request $request)
    {
        $sessionId = $request->get('session_id');
        
        if (!$sessionId) {
            abort(400, 'Session ID is required');
        }

        return response()->stream(function () use ($sessionId) {
            echo "data: " . json_encode(['message' => 'Connected to progress stream']) . "\n\n";
            ob_flush();
            flush();

            $lastProgress = 0;
            while ($lastProgress < 100) {
                $progress = Cache::get("upload_progress_{$sessionId}");
                
                if ($progress && $progress['progress_percent'] > $lastProgress) {
                    echo "data: " . json_encode($progress) . "\n\n";
                    $lastProgress = $progress['progress_percent'];
                    ob_flush();
                    flush();
                }

                if ($lastProgress >= 100) {
                    echo "data: " . json_encode(['message' => 'Upload completed']) . "\n\n";
                    break;
                }

                sleep(1); // Check every second
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no', // Nginx specific
        ]);
    }
}
