<?php

namespace App\Http\Controllers;

use App\Jobs\UploadMovieFile;
use App\Jobs\UploadVideoAndAttachJob;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Languages;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use MongoDB\BSON\ObjectID;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Casts\ObjectId as CastsObjectId;
use SebastianBergmann\Diff\Chunk;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get authenticated user
        $user = auth()->user();

        // Get all maps once for performance
        $genreMap = Genre::all()->pluck('name', '_id')->mapWithKeys(function ($name, $id) {
            return [(string) $id => $name];
        });

        $countryMap = Country::all()->pluck('country', '_id')->mapWithKeys(function ($name, $id) {
            return [(string) $id => $name];
        });

        // Check role and get movies
        if ($user->role === 'channel') {
            // Channel user – show only their channel's videos using their channel_id
            if ($user->channel_id) {
                $channelId = new ObjectId($user->_id);
                $moviesQuery = Movie::where('channel_id', $channelId);
                $movies = $moviesQuery->get();
            } else {
                $movies = collect(); // Empty collection if no channel_id found
            }
        } elseif ($user->role === 'admin') {
            // Check if super admin or specific admin

            // Super admin - show all videos
            $movies = Movie::where('channel_id', new ObjectId($user->_id))->get();
        } else {
            $movies = collect(); // empty collection for other roles
        }

        // Transform movies data consistently for both roles
        if ($movies->isNotEmpty()) {
            $movies = $movies->map(function ($movie) use ($genreMap, $countryMap) {
                $movieData = $movie->toArray();

                // Convert genre ObjectIds to names
                $movieData['genre'] = collect($movieData['genre'] ?? [])->map(function ($genreId) use ($genreMap) {
                    $id = (string) $genreId;
                    return $genreMap[$id] ?? $id;
                })->toArray();

                // Convert country ObjectIds to names
                $movieData['country'] = collect($movieData['country'] ?? [])->map(function ($countryId) use ($countryMap) {
                    $id = (string) $countryId;
                    return $countryMap[$id] ?? $id;
                })->toArray();

                // Ensure language is array of integers for frontend mapping
                $movieData['language'] = collect($movieData['language'] ?? [])->map(function ($lang) {
                    return is_numeric($lang) ? (int) $lang : $lang;
                })->toArray();

                // Ensure boolean fields are properly set
                $movieData['enable_download'] = $movieData['enable_download'] ?? false;
                $movieData['is_paid'] = $movieData['is_paid'] ?? false;
                $movieData['publication'] = $movieData['publication'] ?? false;

                // Ensure description is available
                $movieData['description'] = $movieData['description'] ?? '';
                $movieData['trailer_link'] = $movieData['trailer'] ?? $movieData['trailer_link'] ?? '';

                return $movieData;
            });
        }

        return view('movie.index', compact('movies'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();
        $channels = Channel::all()->toArray();
        $languages = Languages::all()->toArray();
        $countries = Country::all()->toArray();
        return view('movie.create', compact('genres', 'channels', 'languages', 'countries'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'genre' => 'nullable|array|min:1',
            'genre.*' => 'string',
            'pricing' => 'nullable|array',
            'channel_id' => 'nullable|string',
            'release' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'language' => 'nullable|array|min:1',
            'language.*' => 'nullable|integer|between:1,14',
            'country' => 'nullable|array',
            'country.*' => 'string',
            'is_paid' => 'nullable|boolean',
            'publication' => 'nullable|boolean',
            'trailer_link' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'file' => 'nullable|file|mimes:mp4,avi,mov,wmv',
            'enable_download' => 'nullable|boolean',
            'use_global_price' => 'nullable|boolean',
            'stars' => 'nullable|string',
            'director' => 'nullable|array',
            'writer' => 'nullable|array',
            'rating' => 'nullable|string',
            'video_quality' => 'nullable|string',
        ]);

        $isPaid = $request->is_paid ? true : false;
        $price = $isPaid ? ($request->price ?? 0) : 0;

        // Check user role and set is_movie accordingly
        $user = auth()->user();
        $isMovie = $user->role === 'channel' ? true : false; // true for channel, false for admin
        $isChannel = $user->role === 'channel' ? true : false; // true for channel, false for admin
        // Set channel_id based on user role
        // Set channel_id based on user role
        $channelId = new ObjectId($user->channel_id ?? $user->_id);


        try {
            // Upload video to Cloudflare Stream first
            $generatedId = new ObjectId();
            $videoStreamId = null;
            $downloadUrl = null;

            if ($request->hasFile('file')) {
                $videoFile = $request->file('file');

                // Start upload with progress tracking
                $cloudflareService = app(\App\Services\CloudflareStreamService::class);

                $uploadResult = $cloudflareService->uploadToCloudflareStreamWithProgress(
                    $request->file('file'),
                    $request->session()->getId()
                );
                // dd($request->session()->getId(), $uploadResponse);
                // dd($uploadResult);
                if ($uploadResult['success']) {
                    $videoStreamId = $uploadResult['stream_id'];
                    $video_url = $uploadResult['video_url'];
                    $thumbnail = $uploadResult['thumbnail_url'];
                    // Generate download link if enable_download is true
                    if ($request->enable_download) {
                        if ($cloudflareService->waitForVideoReady($videoStreamId)) {
                            $downloadUrl = $cloudflareService->generateDownloadLink($videoStreamId);
                        }
                    }
                    // dd($uploadResult, $downloadUrl);
                } else {
                    // dd($uploadResult);
                    return redirect()->back()->with('error', 'Video upload failed: ' . $uploadResult['error']);
                }
            }

            // Optional language[] if needed
            if ($request->has('language')) {
                foreach ($request->language as $lang) {
                    $multipart[] = [
                        'name' => 'language[]',
                        'contents' => $lang,
                    ];
                }
            }

            // Optional country[] if needed
            if ($request->has('country')) {
                foreach ($request->country as $country) {
                    $multipart[] = [
                        'name' => 'country[]',
                        'contents' => $country,
                    ];
                }
            }

            // This multipart array seems unused, removing for now

            $pricingData = [];
            if (is_null($request->price) && $request->has('prices') && is_array($request->prices)) {
                $countryIds = array_keys($request->prices);

                $objectIds = array_map(fn($id) => new ObjectId($id), $countryIds);

                $countryRecords = Country::whereIn('_id', $objectIds)->get(['_id', 'iso_code']);

                $countries = [];
                foreach ($countryRecords as $record) {
                    $countries[(string)$record->_id] = $record->iso_code;
                }

                foreach ($request->prices as $countryId => $price) {
                    $currencyCode = $countries[$countryId] ?? null;

                    if ($currencyCode && !empty($price)) {
                        $pricingData[] = [
                            'country' => $currencyCode,
                            'price' => (int) $price,
                            '_id' => new ObjectId()
                        ];
                    }
                }
            }

            $movieData = [
                '_id' => $generatedId,
                'title' => $request->title,
                'stars' => $request->stars ?? '',
                'director' => $request->director ?? [],
                'writer' => $request->writer ?? [],
                'rating' => $request->rating ?? '0',
                'country' => $request->country,  // ? array_map(function($countryId) {return new ObjectId($countryId);}, (array)$request->country) : [],
                'description' => $request->description ?? '',
                'genre' => $request->genre ? array_map(function ($genreId) {
                    return new ObjectId($genreId);
                }, (array)$request->genre) : [],
                'language' => $request->language, //? array_map(function($langId) { return new ObjectId($langId)}, (array)$request->language) : [],
                'status' => 'pending', // Add default status
                'video_quality' => $request->video_quality ?? 'HD',
                'video_url' => $video_url ?? '',
                // thumbnail_url will be set conditionally below
                'videoContent_id' => $videoStreamId,
                'pricing' => $pricingData,
                'channel_id' => $channelId,
                'release' => $request->release,
                'price' => (int) $price,
                'is_paid' => $isPaid ? 1 : 0,
                'publication' => $request->publication ? true : false,
                'enable_download' => $request->enable_download ? '1' : '0',
                'download_url' => $downloadUrl,
                'use_global_price' => is_null($request->price) ? false : true,
                'is_movie' => $isMovie,
                'isChannel' =>  $isChannel,
                'is_tvseries' => 0,
                'stream_id' => $videoStreamId,
                'total_rating' => 0,
                'today_view' => 0,
                'weekly_view' => 0,
                'monthly_view' => 0,
                'total_view' => 0,
                'last_ep_added' => now(),
                'cre' => now(), // created timestamp
                'created_at' => now(),
                'updated_at' => now(),
                'videos_id' => $generatedId
            ];

            // dd($movieData);
            // Language and country are already handled above with ObjectId conversion

            if ($request->trailer_link) {
                // If trailer is provided as URL
                $movieData['trailer_link'] = $request->trailer_link;
            }

            // Handle file uploads (thumbnail, poster)
            $hasManualThumbnail = false;
            $urlFiles = ['thumbnail', 'poster'];
            foreach ($urlFiles as $fileField) {
                if ($request->hasFile($fileField)) {
                    $filename = time() . '_' . $request->file($fileField)->getClientOriginalName();
                    $path = $request->file($fileField)->storeAs('banners', $filename, 'public');
                    $url = asset('storage/' . $path);
                    $movieData[$fileField . '_url'] = $url;

                    // Track if manual thumbnail was uploaded
                    if ($fileField === 'thumbnail') {
                        $hasManualThumbnail = true;
                    }
                }
            }

            // Only use Cloudflare thumbnail if no manual thumbnail was uploaded
            if (!$hasManualThumbnail && isset($thumbnail) && !empty($thumbnail)) {
                $movieData['thumbnail_url'] = $thumbnail;
            }

            // Save to MongoDB
            $movie = new Movie($movieData);
            //dd($movie);
            $movie->save();
            $VideoObjectId = $movie->_id;
            $movie->videos_id = new ObjectId($VideoObjectId);
            if ($isChannel == true) {
                $movie->isChannel = true;
            } else {
                $movie->isChannel = false;
            }
            $movie->save();

            // Send push notification for new movie upload
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->handleContentUpload(
                    channelId: (string)$channelId,
                    contentType: 'movie',
                    title: $movie->title,
                    thumbnailUrl: $movie->thumbnail_url ?? null
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send movie upload notification', [
                    'movie_id' => $movie->_id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('content.movies.upload-video', $movie->_id)->with('success', 'Movie created successfully! Now upload video.');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Show video upload form for a movie
     */
    public function uploadVideo($id)
    {
        $movie = $this->findMovieById($id, ['channel']);

        // Load genre and language names for display
        if (!empty($movie->genre)) {
            $genreIds = is_array($movie->genre) ? $movie->genre : [$movie->genre];
            $genres = \App\Models\Genre::whereIn('_id', $genreIds)->pluck('name')->toArray();
            $movie->genre_names = implode(', ', $genres);
        }

        if (!empty($movie->language)) {
            // Language is stored as static string/array, so display directly
            if (is_array($movie->language)) {
                $movie->language_names = implode(', ', $movie->language);
            } else {
                $movie->language_names = $movie->language;
            }
        }

        if (!empty($movie->country)) {
            // Country might be stored as static string/array, handle both cases
            if (is_array($movie->country) && !empty($movie->country[0])) {
                // Check if first element is ObjectId or string
                if (is_object($movie->country[0]) && method_exists($movie->country[0], '__toString')) {
                    // It's ObjectId, fetch from database
                    $countries = \App\Models\Country::whereIn('_id', $movie->country)->pluck('name')->toArray();
                    $movie->country_names = implode(', ', $countries);
                } else {
                    // It's string array, display directly
                    $movie->country_names = implode(', ', $movie->country);
                }
            } else if (!is_array($movie->country)) {
                // Single string value
                $movie->country_names = $movie->country;
            }
        }

        // Check if user has permission to upload video for this movie
        $user = Auth::user();
        if ($user->role === 'channel' && $movie->channel_id != $user->_id) {
            return redirect()->route('content.movies.index')->with('error', 'You can only upload videos for your own movies.');
        }

        return view('movie.upload-video-simple', compact('movie'));
    }

    /**
     * Generate signed upload URL for direct client-to-Cloudflare upload
     */
    public function generateUploadUrl(Request $request, $id)
    {
        try {
            $movie = $this->findMovieById($id);

            // Check permissions
            $user = Auth::user();
            if ($user->role === 'channel' && $movie->channel_id != $user->_id) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'filename' => 'required|string',
                'filesize' => 'required|integer|min:1|max:3221225472', // Max 3GB
                'filetype' => 'required|string'
            ]);

            $cloudflareService = app(\App\Services\CloudflareStreamService::class);

            $metadata = [
                'name' => $request->filename,
                'size' => $request->filesize,
                'type' => $request->filetype
            ];

            $result = $cloudflareService->generateSignedUploadUrl($metadata);

            if ($result['success']) {
                // Update movie with stream_id for webhook handling
                $movie->update([
                    'stream_id' => $result['stream_id'],
                    'status' => 'uploading'
                ]);

                \Log::info('Generated upload URL for movie', [
                    'movie_id' => $movie->_id,
                    'stream_id' => $result['stream_id'],
                    'file_size' => $this->formatBytes($request->filesize)
                ]);

                return response()->json([
                    'success' => true,
                    'upload_url' => $result['upload_url'],
                    'stream_id' => $result['stream_id'],
                    'chunk_size' => $this->calculateOptimalChunkSize($request->filesize),
                    'api_token' => config('services.cloudflare.CLOUDFLARE_API_TOKEN'),
                    'account_id' => config('services.cloudflare.CLOUDFLARE_ACCOUNT_ID'),
                    'endpoint' => $result['endpoint'] ?? null
                ]);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Failed to generate upload URL', [
                'movie_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate upload URL: ' . $e->getMessage(),
                'debug_info' => app()->environment('local') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    /**
     * Handle upload completion and update movie status
     */
    public function handleUploadComplete(Request $request, $id)
    {
        try {
            $movie = $this->findMovieById($id);

            $request->validate([
                'stream_id' => 'required|string',
                'upload_url' => 'required|string'
            ]);

            // Update movie status to processing
            $movie->update([
                'status' => 'processing',
                'video_upload_completed_at' => now()
            ]);

            \Log::info('Upload completed for movie', [
                'movie_id' => $movie->_id,
                'stream_id' => $request->stream_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Upload completed successfully! Video is being processed.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to handle upload completion', [
                'movie_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update movie status'
            ], 500);
        }
    }

    /**
     * Calculate optimal chunk size based on file size
     */
    private function calculateOptimalChunkSize($fileSize)
    {
        if ($fileSize < 50 * 1024 * 1024) { // < 50MB
            return min($fileSize, 8 * 1024 * 1024); // 8MB or file size
        } else if ($fileSize < 500 * 1024 * 1024) { // < 500MB
            return 32 * 1024 * 1024; // 32MB
        } else if ($fileSize < 1024 * 1024 * 1024) { // < 1GB
            return 64 * 1024 * 1024; // 64MB
        } else {
            return 256 * 1024 * 1024; // 256MB for 2GB+ files (max optimization)
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Store video file for a movie
     */
    public function storeVideo(Request $request, $id)
    {
        // Check PHP upload limits before validation
        $maxUploadSize = min(
            $this->parseSize(ini_get('post_max_size')),
            $this->parseSize(ini_get('upload_max_filesize'))
        );

        $maxUploadSizeMB = round($maxUploadSize / (1024 * 1024));

        \Log::info('PHP Upload Limits Check', [
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_upload_mb' => $maxUploadSizeMB
        ]);

        $request->validate([
            'video_file' => "required|file|mimes:mp4,avi,mkv,mov,wmv,flv|max:3072000", // 3GB max (supports 2GB files + buffer)
        ]);

        $movie = $this->findMovieById($id);

        // Check if user has permission to upload video for this movie
        $user = Auth::user();
        if ($user->role === 'channel' && $movie->channel_id != $user->_id) {
            return redirect()->route('content.movies.index')->with('error', 'You can only upload videos for your own movies.');
        }

        try {
            if ($request->hasFile('video_file')) {
                $sessionId = $request->input('session_id', $request->session()->getId());

                \Log::info('🚀 Starting simple Cloudflare upload', [
                    'movie_id' => $id,
                    'file_name' => $request->file('video_file')->getClientOriginalName(),
                    'file_size' => $this->formatBytes($request->file('video_file')->getSize())
                ]);

                // Use simple upload service
                $cloudflareService = app(\App\Services\CloudflareStreamServiceSimple::class);
                $uploadResult = $cloudflareService->uploadVideo($request->file('video_file'), $sessionId);

                if ($uploadResult['success']) {
                    // Update movie with Cloudflare details
                    $movie->update([
                        'videoContent_id' => $uploadResult['stream_id'],
                        'stream_id' => $uploadResult['stream_id'],
                        'video_url' => $uploadResult['video_url'],
                        'thumbnail_url' => $uploadResult['thumbnail_url'] ?? $movie->thumbnail_url,
                        'status' => 'ready'
                    ]);

                    // Generate download URL if enabled
                    if ($movie->enable_download && $movie->enable_download !== '0') {
                        $downloadUrl = $cloudflareService->generateDownloadLink($uploadResult['stream_id']);
                        if ($downloadUrl) {
                            $movie->update(['download_url' => $downloadUrl]);
                        }
                    }

                    \Log::info('✅ Upload completed successfully', [
                        'movie_id' => $movie->_id,
                        'stream_id' => $uploadResult['stream_id']
                    ]);

                    return redirect()->route('content.movies.index')
                        ->with('success', 'Video uploaded successfully to Cloudflare!');
                } else {
                    \Log::error('❌ Upload failed', [
                        'movie_id' => $id,
                        'error' => $uploadResult['error']
                    ]);

                    return redirect()->back()
                        ->with('error', 'Upload failed: ' . $uploadResult['error']);
                }
            }

            return redirect()->back()->with('error', 'Please select a video file to upload.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error uploading video: ' . $e->getMessage());
        }
    }

    /**
     * Process Cloudflare upload directly in background
     */
    private function processCloudflareUploadDirectly($movie, $localPath, $sessionId)
    {
        // Create a simple background process using exec
        $scriptPath = base_path('background-upload.php');

        // Create the background script if it doesn't exist
        $this->createBackgroundUploadScript($scriptPath);

        $command = sprintf(
            'php %s %s %s %s > /dev/null 2>&1 &',
            $scriptPath,
            $movie->_id,
            $localPath,
            $sessionId
        );

        \Log::info('Starting direct background upload', [
            'command' => $command,
            'movie_id' => $movie->_id,
            'local_path' => $localPath
        ]);

        exec($command);
    }

    /**
     * Create background upload script for direct execution
     */
    private function createBackgroundUploadScript($scriptPath)
    {
        if (!file_exists($scriptPath)) {
            $script = '<?php
require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$movieId = $argv[1] ?? null;
$localPath = $argv[2] ?? null;
$sessionId = $argv[3] ?? null;

if ($movieId && $localPath && $sessionId) {
    try {
        $job = new App\Jobs\CloudflareUploadJob($movieId, $localPath, $sessionId);
        $job->handle();
        echo "Background upload completed successfully\n";
    } catch (Exception $e) {
        echo "Background upload failed: " . $e->getMessage() . "\n";
        file_put_contents("storage/logs/background-upload-error.log", date("Y-m-d H:i:s") . " - " . $e->getMessage() . "\n", FILE_APPEND);
    }
} else {
    echo "Missing arguments\n";
}
';
            file_put_contents($scriptPath, $script);
            chmod($scriptPath, 0755);
        }
    }

    /**
     * Fallback method for local video upload when Cloudflare fails
     */
    private function fallbackLocalUpload($request, $movie, $sessionId)
    {
        try {
            \Log::info('Using fallback local upload for movie: ' . $movie->_id);

            $file = $request->file('video_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('movies/videos', $filename, 'public');
            $videoUrl = asset('storage/' . $path);

            // Update cache to show completion
            $cacheKey = "upload_progress_{$sessionId}";
            \Cache::put($cacheKey, [
                'percent' => 100,
                'uploaded' => $file->getSize(),
                'total' => $file->getSize(),
                'status' => 'completed',
                'message' => 'Upload completed (local storage fallback)!'
            ], now()->addMinutes(5));

            // Update movie with local video details
            $movie->update([
                'video_url' => $videoUrl,
                'video_file' => $path,
                'status' => 'pending'
            ]);

            return redirect()->route('content.movies.index')
                ->with('warning', 'Video uploaded to local storage as Cloudflare is unavailable. Video may not stream optimally.');
        } catch (\Exception $e) {
            \Log::error('Fallback upload also failed', [
                'error' => $e->getMessage(),
                'movie_id' => $movie->_id
            ]);

            return redirect()->back()->with('error', 'Both Cloudflare and local upload failed. Please try again later.');
        }
    }

    /**
     * Get upload progress for AJAX calls
     */
    public function getUploadProgress(Request $request)
    {
        $sessionId = $request->get('session_id', session()->getId());
        $cacheKey = "upload_progress_{$sessionId}";

        // Use static hashmap for faster access (DSA optimization)
        static $progressHashMap = [];
        static $lastAccess = [];

        $now = time();
        $hashKey = md5($sessionId);

        // Check if we have recent data in static hashmap (avoids cache lookup)
        if (
            isset($progressHashMap[$hashKey]) &&
            isset($lastAccess[$hashKey]) &&
            ($now - $lastAccess[$hashKey]) < 2
        ) {
            // Return cached data if accessed within last 2 seconds
            return response()->json($progressHashMap[$hashKey]);
        }

        // Efficient cache lookup
        $progress = \Cache::get($cacheKey, [
            'percent' => 0,
            'uploaded' => 0,
            'total' => 0,
            'status' => 'waiting',
            'message' => 'Waiting to start upload...',
            'timestamp' => $now
        ]);

        // Store in static hashmap for subsequent fast access
        $progressHashMap[$hashKey] = $progress;
        $lastAccess[$hashKey] = $now;

        // Cleanup old entries (memory management)
        if (count($progressHashMap) > 100) {
            foreach ($lastAccess as $key => $time) {
                if (($now - $time) > 300) { // Remove entries older than 5 minutes
                    unset($progressHashMap[$key]);
                    unset($lastAccess[$key]);
                }
            }
        }

        // Only log significant changes to reduce log noise
        if ($progress['percent'] % 10 == 0 || $progress['status'] !== 'uploading') {
            \Log::info('Progress milestone', [
                'session_id' => substr($sessionId, 0, 8) . '...',
                'percent' => $progress['percent'],
                'status' => $progress['status']
            ]);
        }

        return response()->json($progress);
    }




    /**
     * Display the specified resource.
     */
    public function show(Movie $movieId)
    {
        $movie = Movie::findOrFail($movieId);
        return view('movie.show', compact('movie'));

        //        dd("hello");
        //        return view('movie.show', compact('movie'), [
        //            'movies' => Movie::where('_id', )->get()
        //        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movie $movie)
    {
        $genres = Genre::all();
        $channels = Channel::all()->toArray();
        $languages = Languages::all()->toArray();
        $countries = Country::all()->toArray();

        return view('movie.edit', compact('movie', 'genres', 'channels', 'languages', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        $request->validate([
            'title' => 'required|string',
            'file' => 'nullable|file',
        ]);

        $movie->title = $request->title ?? 'updatedValue';
        $video_file = $request->hasFile('file');


        if ($video_file) {
            $cloudflareService = app(\App\Services\CloudflareStreamService::class);

            // Step 1: Delete existing video from Cloudflare
            if (!empty($movie->videoContent_id)) {
                $cloudflareService->deleteVideo($movie->videoContent_id);
            }

            // Step 2: Upload new video to Cloudflare
            $uploadResult = $cloudflareService->uploadToCloudflareStreamWithProgress(
                $request->file('file'),
                $request->session()->getId()
            );

            // Step 3: Save new video details if successful
            if ($uploadResult['success']) {
                $movie->video_url = $uploadResult['video_url'] ?? null;
                if (!empty($movie->thumbnail_url)) {
                    $movie->thumbnail_url = $uploadResult['thumbnail_url'] ?? null;
                }

                $movie->videoContent_id = $uploadResult['stream_id'] ?? null;

                if ($cloudflareService->waitForVideoReady($movie->videoContent_id)) {
                    $downloadUrl = $cloudflareService->generateDownloadLink($movie->videoContent_id);
                }
                $movie->download_url = $downloadUrl ?? null;
            } else {
                return back()->with('error', 'Video upload failed: ' . ($uploadResult['error'] ?? 'Unknown error'));
            }
        }

        $movie->save();

        return back()->with('success', 'Movie updated successfully.');
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $movie = $this->findMovieById($id);

            // Check permissions
            $user = Auth::user();
            if ($user->role === 'channel' && $movie->channel_id != $user->_id) {
                return redirect()->back()->with('error', 'You can only delete your own movies.');
            }

            $streamId = $movie->videoContent_id ?? null;
            if (!empty($streamId)) {
                $cloudflareService = app(\App\Services\CloudflareStreamService::class);
                $deleteResponse = $cloudflareService->deleteVideo($streamId);

                // Log the response for debugging
                \Log::info('Cloudflare deletion response', [
                    'stream_id' => $streamId,
                    'response' => $deleteResponse
                ]);

                // Continue even if Cloudflare deletion fails - still delete from DB
                if (!isset($deleteResponse['success']) || $deleteResponse['success'] === false) {
                    \Log::warning('Cloudflare deletion failed but continuing with DB deletion', [
                        'stream_id' => $streamId,
                        'response' => $deleteResponse,
                    ]);
                }
            }

            $movie->delete();

            return redirect()->route('content.movies.index')->with('success', 'Movie deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Movie deletion failed', [
                'error' => $e->getMessage(),
                'movie_id' => $movie->_id ?? null
            ]);

            return redirect()->back()->with('error', 'Failed to delete movie: ' . $e->getMessage());
        }
    }

    /**
     * Check if the user is a super admin with full access
     */
    private function isSuperAdmin($user)
    {
        return $user->email === 'admin@multiplexplay.com' ||
            $user->is_super_admin === true ||
            $user->admin_level === 'super' ||
            in_array($user->email, [
                'superadmin@multiplexplay.com',
                'admin@example.com',
            ]);
    }

    /**
     * Find movie by ID, handling MongoDB ObjectId properly
     */
    private function findMovieById($id, $with = [])
    {
        if (strlen($id) === 24 && ctype_xdigit($id)) {
            $query = Movie::where('_id', new ObjectId($id));
            if (!empty($with)) {
                $query->with($with);
            }
            return $query->firstOrFail();
        } else {
            $query = Movie::query();
            if (!empty($with)) {
                $query->with($with);
            }
            return $query->findOrFail($id);
        }
    }

    /**
     * Parse PHP size values (like 127M, 2G) to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
}
