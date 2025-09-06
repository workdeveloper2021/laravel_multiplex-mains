<?php

namespace App\Http\Controllers;

use App\Models\Episodes;
use App\Models\Seasons;
use App\Models\WebSeries;
use Illuminate\Http\Request;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Casts\ObjectId as CastsObjectId;

class EpisodeController extends Controller
{
    /**
     * Display episodes for a specific season
     */
    public function index($seasonId)
    {
        $season = Seasons::findOrFail($seasonId);
        $webseries = WebSeries::findOrFail($season->webSeries);

        // Get episodes for this season
        $episodes = Episodes::where('seasonId', new ObjectId($seasonId))
                          ->orderBy('episode_number', 'asc')
                          ->get();

        return view('webseries.episodes.index', compact('season', 'webseries', 'episodes'));
    }

    /**
     * Show the form for creating a new episode
     */
    public function create($seasonId)
    {
        $season = Seasons::findOrFail($seasonId);
        $webseries = WebSeries::findOrFail($season->webSeries);
        $channels = \App\Models\Channel::all()->toArray();

        return view('webseries.episodes.create', compact('season', 'webseries', 'channels'));
    }

    /**
     * Store a newly created episode
     */
    public function store(Request $request, $seasonId)
    {
        $request->validate([
        'title' => 'required|string|max:255',
        'episode_number' => 'required|integer|min:1',
        'duration' => 'nullable|integer|min:1',
        'file' => 'required|file|mimes:mp4,avi,mov,wmv', // 1GB max
        'enable_download' => 'nullable|boolean',
            'channel_id' => 'nullable|string',
        ]);

        $season = Seasons::findOrFail($seasonId);

        // Get authenticated user and handle role-based logic
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return redirect()->back()->with('error', 'Access denied. Only admins can manage episodes.');
        }

        // Set channel_id based on user role (same as webseries logic)
        $channelId = $user->role === 'admin' ? $request->channel_id : $user->_id;
        if ($channelId && is_string($channelId)) {
            $channelId = new ObjectId($channelId);
        }

        try {
            // Upload video to Cloudflare Stream first
            $generatedId = new ObjectId();
            $videoStreamId = null;
            $downloadUrl = null;
            $videoUrl = null;
            $thumbnailUrl = null;

            if ($request->hasFile('file')) {
                $videoFile = $request->file('file');

                // Start upload with progress tracking
                $cloudflareService = app(\App\Services\CloudflareStreamService::class);

                $uploadResult = $cloudflareService->uploadToCloudflareStreamWithProgress(
                    $request->file('file'),
                    $request->session()->getId()
                );

                if ($uploadResult['success']) {
                    $videoStreamId = $uploadResult['stream_id'];
                    $videoUrl = $uploadResult['video_url'];
                    $thumbnailUrl = $uploadResult['thumbnail_url'];

                    // Generate download link if enable_download is true
                    if ($request->enable_download) {
                        if ($cloudflareService->waitForVideoReady($videoStreamId)) {
                            $downloadUrl = $cloudflareService->generateDownloadLink($videoStreamId);
                        }
                    }
                } else {
                    return redirect()->back()->with('error', 'Video upload failed: ' . $uploadResult['error']);
                }
            }

            // Create episode data matching your MongoDB structure
            $episodeData = [
                '_id' => $generatedId,
                'title' => $request->title,
                'episode_number' => (string) $request->episode_number, // Convert to string like your example
                'duration' => $request->duration ?? null,
                'seasonId' => new ObjectId($seasonId),
                'channel_id' => $channelId,
                'video_url' => $videoUrl ?? '',
                'videoContent_id' => $videoStreamId,
                'thumbnail_url' => $thumbnailUrl ?? '',
                'enable_download' => $request->enable_download ? '1' : '0',
                'download_url' => $downloadUrl ?? '',
                'createdAt' => now(),
                'updatedAt' => now(),
            ];

            // Create new episode using mass assignment
            $episode = Episodes::create($episodeData);

            // Update season to include this episode
            $season->push('episodesId', $episode->_id);

            // Send push notification for new episode upload
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->handleContentUpload(
                    channelId: (string)$channelId,
                    contentType: 'episode',
                    title: $episode->title,
                    thumbnailUrl: $episode->thumbnail ?? null
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send episode upload notification', [
                    'episode_id' => $episode->_id,
                    'error' => $e->getMessage()
                ]);
            }

            // Check if it's an AJAX request
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Episode created successfully.',
                    'redirect_url' => route('content.seasons.episodes.index', $seasonId)
                ]);
            }

            return redirect()->route('content.seasons.episodes.index', $seasonId)
                            ->with('success', 'Episode created successfully.');

        } catch (\Exception $e) {
            // Check if it's an AJAX request
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create episode: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to create episode: ' . $e->getMessage());
        }
    }

    /**
     * Get upload progress for AJAX calls
     */
    public function getUploadProgress(Request $request)
    {
        $sessionId = $request->get('session_id', session()->getId());
        $cacheKey = "upload_progress_{$sessionId}";

        $progress = \Cache::get($cacheKey, [
            'percent' => 0,
            'uploaded' => 0,
            'total' => 0,
            'status' => 'waiting',
            'message' => 'Waiting to start upload...'
        ]);

        return response()->json($progress);
    }

    /**
     * Show the form for editing the specified episode
     */
    public function edit($episodeId)
    {
        $episode = Episodes::findOrFail($episodeId);
        $season = Seasons::findOrFail($episode->seasonId);
        $webseries = WebSeries::findOrFail($season->webSeries);
        $channels = \App\Models\Channel::all()->toArray();

        // Check if current user is channel user and episode belongs to them
        $user = auth()->user();
        if ($user->role === 'channel') {
            // For channel users, ensure they can only edit their own episodes
            if ((string)$episode->channel_id !== (string)$user->_id) {
                return redirect()->route('seasons.episodes.index', $episode->seasonId)
                    ->with('error', 'You can only edit your own episodes.');
            }
        }

        return view('webseries.episodes.edit', compact('episode', 'season', 'webseries', 'channels'));
    }

    /**
     * Update the specified episode
     */
    public function update(Request $request, $episodeId)
    {
        $request->validate([
        'title' => 'required|string|max:255',
        'episode_number' => 'required|integer|min:1',
        'duration' => 'nullable|integer|min:1',
        'file' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:1048576',
            'channel_id' => 'nullable|string',
        ]);

        $episode = Episodes::findOrFail($episodeId);

        // Get authenticated user and handle role-based logic
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return redirect()->back()->with('error', 'Access denied. Only admins can manage episodes.');
        }

        // Set channel_id based on user role (same as webseries logic)
        $channelId = $user->role === 'admin' ? $request->channel_id : $user->_id;
        if ($channelId && is_string($channelId)) {
            $channelId = new ObjectId($channelId);
        }

        try {
            // Handle video file update if provided
            if ($request->hasFile('file')) {
                $cloudflareService = app(\App\Services\CloudflareStreamService::class);

                // Delete existing video if it exists
                if (!empty($episode->videoContent_id)) {
                    $cloudflareService->deleteVideo($episode->videoContent_id);
                }

                // Upload new video
                $uploadResult = $cloudflareService->uploadToCloudflareStreamWithProgress(
                    $request->file('file'),
                    $request->session()->getId()
                );

                if ($uploadResult['success']) {
                    $episode->videoContent_id = $uploadResult['stream_id'];
                    $episode->video_url = $uploadResult['video_url'];
                    $episode->thumbnail_url = $uploadResult['thumbnail_url'];

                    // Generate download link if enable_download is true
                    if ($request->enable_download) {
                        if ($cloudflareService->waitForVideoReady($episode->videoContent_id)) {
                            $episode->download_url = $cloudflareService->generateDownloadLink($episode->videoContent_id);
                        }
                    }
                } else {
                    return redirect()->back()->with('error', 'Video upload failed: ' . $uploadResult['error']);
                }
            }

            // Update episode details
            $episode->title = $request->title;
            $episode->episode_number = $request->episode_number;
            $episode->duration = $request->duration;
            $episode->channel_id = $channelId;
            $episode->enable_download = $request->enable_download ? '1' : '0';
            $episode->save();

            return redirect()->route('seasons.episodes.index', $episode->seasonId)
                            ->with('success', 'Episode updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update episode: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified episode
     */
    public function destroy($episodeId)
    {
        $episode = Episodes::findOrFail($episodeId);
        $seasonId = $episode->seasonId;

        try {
            // Delete video from Cloudflare
            if (!empty($episode->videoContent_id)) {
                $cloudflareService = app(\App\Services\CloudflareStreamService::class);
                $cloudflareService->deleteVideo($episode->videoContent_id);
            }

            // Remove episode from season
            $season = Seasons::findOrFail($seasonId);
            $season->pull('episodesId', $episode->_id);

            $episode->delete();

            return redirect()->route('seasons.episodes.index', $seasonId)
                            ->with('success', 'Episode deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete episode: ' . $e->getMessage());
        }
    }
}
