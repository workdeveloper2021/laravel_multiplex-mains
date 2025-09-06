<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class FrontMovieController extends Controller
{
    private function country(): string
    {
        $cc = strtoupper((string) session('country_code', 'IN'));
        return preg_match('/^[A-Z]{2}$/', $cc) ? $cc : 'IN';
    }

    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access movies.');
        }
        return view('FrontendPlayer.simple_secure', [
            'pageTitle' => 'MultiplexPlay – Your Ultimate OTT Experience',
        ]);
    }

    /**
     * HOME: proxy + server-side shaping so the view stays dumb.
     * Returns:
     * {
     *   sections: [
     *     { name, movies: [CardDTO], series: [CardDTO] }
     *   ],
     *   autoplay: { title, url } | null
     * }
     */
    public function homeData()
    {
        $country = $this->country();
        $homeUrl = rtrim(env('NODE_API_URL'), '/') . '/home_content_for_android';

        try {
            $res = Http::timeout(12)->withHeaders([
                'api-key' => env('NODE_API_KEY'),
                'Accept'  => 'application/json',
            ])->get($homeUrl, ['country' => $country]);

            if (!$res->successful()) {
                Log::warning('homeData upstream failed', ['status' => $res->status()]);
                return response()->json(['message' => 'Failed to load home content'], 502);
            }

            $payload  = $res->json();
            $features = $payload['features_genre_and_movie'] ?? [];

            // Normalize to sections with movies/series already split
            $sections = [];
            $autoplay = null;

            foreach ($features as $section) {
                $name   = $section['name'] ?? ($section['genre_name'] ?? 'Featured');
                $videos = $section['videos'] ?? [];
                if (!is_array($videos) || !count($videos)) {
                    $sections[] = ['name' => $name, 'movies' => [], 'series' => []];
                    continue;
                }

                $movies = [];
                $series = [];

                foreach ($videos as $v) {
                    $dto = [
                        'title'        => $v['title'] ?? 'Untitled',
                        'is_tvseries'  => (string)($v['is_tvseries'] ?? '0'),
                        'videos_id'    => $v['videos_id'] ?? ($v['_id'] ?? ($v['id'] ?? null)),
                        'channel_id'   => $v['channel_id'] ?? '',
                        'poster_url'   => $v['poster_url'] ?? null,
                        'thumbnail_url' => $v['thumbnail_url'] ?? null,
                        'video_quality' => $v['video_quality'] ?? 'SD',
                        'is_paid'      => (string)($v['is_paid'] ?? '0'),
                        // optional hints; actual URL still gated by check endpoints
                        'hint_url'     => $v['video_url'] ?? ($v['stream_url'] ?? null),
                    ];

                    if ($dto['is_tvseries'] === '1') $series[] = $dto;
                    else {
                        $movies[] = $dto;

                        // Pick first playable movie as autoplay candidate (best-effort)
                        if ($autoplay === null && !empty($dto['hint_url'])) {
                            $autoplay = [
                                'title' => $dto['title'],
                                'url'   => $dto['hint_url'],
                            ];
                        }
                    }
                }

                $sections[] = [
                    'name'   => $name,
                    'movies' => $movies,
                    'series' => $series,
                ];
            }

            return response()->json([
                'sections' => $sections,
                'autoplay' => $autoplay, // {title,url} or null
            ], 200);
        } catch (\Throwable $e) {
            Log::error('homeData error: ' . $e->getMessage());
            return response()->json(['message' => 'Home content error'], 500);
        }
    }

    /**
     * MOVIE: subscription + access check (normalized for the view)
     */
    public function movie(Request $request)
    {
        $vId        = $request->query('vId');
        $channel_id = $request->query('channel_id');
        $user_id    = auth()->id();

        if (!$vId || !$channel_id) {
            return response()->json(['message' => 'Missing vId or channel_id'], 422);
        }

        $device_id = substr(hash('sha256', $request->ip() . '|' . $user_id), 0, 16);

        $url = rtrim(env('NODE_API_URL'), '/') . '/movies';
        $query = [
            'country'    => $this->country(),
            'device_id'  => $device_id,
            'vId'        => $vId,
            'user_id'    => $user_id,
            'channel_id' => $channel_id,
        ];

        try {
            $res = Http::timeout(15)->withHeaders([
                'api-key' => env('NODE_API_KEY'),
                'Accept'  => 'application/json',
            ])->get($url, $query);

            if (!$res->successful()) {
                Log::warning('movie upstream failed', ['status' => $res->status(), 'query' => $query]);
                return response()->json(['message' => 'Failed to fetch movie details'], 502);
            }

            $data = $res->json();

            return response()->json([
                'message'          => $data['message'] ?? '',
                'allowVideoAccess' => (bool)($data['allowVideoAccess'] ?? false),
                'isSubscribed'     => (bool)($data['isSubscribed'] ?? false),
                'userSubscribed'   => (bool)($data['userSubscribed'] ?? false),
                'data'             => $data['data'][0] ?? null, // single object for the view
                'related_movie'    => $data['related_movie'] ?? [],
                'country'          => $this->country(),
            ]);
        } catch (\Throwable $e) {
            Log::error('movie error: ' . $e->getMessage());
            return response()->json(['message' => 'Movie fetch error'], 500);
        }
    }

    /**
     * WEBSERIES: subscription + access check (pass-through)
     * View renders seasons/episodes from this payload.
     */
    public function webseries(Request $request)
    {
        $seriesId   = $request->query('id');
        $channel_id = $request->query('channel_id');
        $user_id    = auth()->id();

        if (!$seriesId) {
            return response()->json(['message' => 'Missing webseries id'], 422);
        }

        $url = rtrim(env('NODE_API_URL'), '/') . '/webseries/details';
        $query = [
            'id'         => $seriesId,
            'field'      => '_id',
            'user_id'    => $user_id,
            'channel_id' => $channel_id,
            'country'    => $this->country(),
        ];

        try {
            $res = Http::timeout(15)->withHeaders([
                'api-key' => env('NODE_API_KEY'),
                'Accept'  => 'application/json',
            ])->get($url, $query);

            if (!$res->successful()) {
                Log::warning('webseries upstream failed', ['status' => $res->status(), 'query' => $query]);
                return response()->json(['message' => 'Failed to fetch webseries details'], 502);
            }

            return response()->json($res->json(), 200);
        } catch (\Throwable $e) {
            Log::error('webseries error: ' . $e->getMessage());
            return response()->json(['message' => 'Webseries fetch error'], 500);
        }
    }

    // ---------------- HLS manifest proxy ----------------

    private function validateShortToken(?string $token, int $ttlSeconds = 90): bool
    {
        if (!$token) return false;
        try {
            $decoded = json_decode(base64_decode($token, true), true, 512, JSON_THROW_ON_ERROR);
            $ts = (int)($decoded['timestamp'] ?? 0);
            if ($ts <= 0) return false;
            $now = time();
            return ($now >= $ts) && (($now - $ts) <= $ttlSeconds);
        } catch (\Throwable $e) {
            Log::warning('Bad HLS token: ' . $e->getMessage());
            return false;
        }
    }

    public function videoManifest(Request $request)
    {
        $url   = $request->query('url');
        $token = $request->query('token');

        if (!$this->validateShortToken($token)) {
            return response('Unauthorized manifest request', 401);
        }

        if (!$url || !Str::startsWith($url, ['http://', 'https://'])) {
            return response('Invalid URL', 422);
        }

        try {
            $upstream = Http::timeout(12)->withHeaders([
                'Accept' => 'application/vnd.apple.mpegurl,application/x-mpegURL,*/*',
            ])->get($url);

            if (!$upstream->successful()) {
                Log::warning('Manifest upstream failed', ['status' => $upstream->status(), 'url' => $url]);
                return response('Upstream fetch failed', 502);
            }

            return Response::make($upstream->body(), 200, [
                'Content-Type'        => 'application/vnd.apple.mpegurl',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
                'Content-Disposition' => 'inline; filename="index.m3u8"',
            ]);
        } catch (\Throwable $e) {
            Log::error('videoManifest error: ' . $e->getMessage());
            return response('Manifest proxy error', 500);
        }
    }

    /**
     * Optional unified dispatcher if you want one endpoint later.
     */
    public function check(Request $request)
    {
        $isTv = $request->query('is_tvseries', null);
        if ($isTv === null) return response()->json(['message' => 'Missing is_tvseries'], 422);

        $isTv = filter_var($isTv, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($isTv === null) return response()->json(['message' => 'Invalid is_tvseries'], 422);

        if ($isTv === false) {
            $request->merge([
                'vId'        => $request->query('vId') ?: $request->query('id') ?: $request->query('_id'),
                'channel_id' => $request->query('channel_id'),
            ]);
            return $this->movie($request);
        }

        $request->merge([
            'id'         => $request->query('id') ?: $request->query('vId') ?: $request->query('_id'),
            'field'      => '_id',
            'channel_id' => $request->query('channel_id'),
        ]);
        return $this->webseries($request);
    }
}
