<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloudflareWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Optionally verify signature from Cloudflare if configured.
        $payload = $request->all();
        Log::info('CF Stream webhook', $payload);

        $uid = $payload['uid'] ?? $payload['data']['uid'] ?? null;
        if (!$uid) return response()->json(['ok' => true]);

        $movie = Movie::where('cloudflare_stream_id', $uid)->first();
        if (!$movie) return response()->json(['ok' => true]);

        // Events: "video.created", "video.ready", "video.encoding.progress", etc.
        $eventType = $payload['type'] ?? '';
        if ($eventType === 'video.ready') {
            $playback = $payload['data']['playback'] ?? [];
            $movie->video_url     = $playback['hls'] ?? ($playback['dash'] ?? $movie->video_url);
            $movie->thumbnail_url = $payload['data']['thumbnail'] ?? $movie->thumbnail_url;
            $movie->status        = 'ready';
            $movie->save();
        }

        return response()->json(['ok' => true]);
    }
}
