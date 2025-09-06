<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiProxyController extends Controller
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.multiplex.base_url', 'https://multiplexplay.com'), '/');
        // Ensure api-key is always present (fallback to provided default)
        $this->apiKey = config('services.multiplex.api_key', env('MULTIPLEX_API_KEY')) ?: 'ec8590cb04e0e37c6706ab6c';
    }

    private function client()
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey,
            'Accept'  => 'application/json',
        ])->timeout(30);
    }

    public function homeContent(Request $request)
    {
        try {
            $country = $request->query('country', 'IN');
            $res = $this->client()->get($this->baseUrl . '/nodeapi/rest-api/v130/home_content_for_android', [
                'country' => $country,
            ]);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('homeContent failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function checkMovieSubscription(Request $request)
    {
        try {
            $vId = $request->query('vId');
            $userId = $request->query('user_id');
            $channelId = $request->query('channel_id');
            $country = $request->query('country', 'IN');
            
            $url = $this->baseUrl . '/nodeapi/rest-api/v130/movies';
            $res = $this->client()->get($url, [
                'country' => $country,
                'vId' => $vId,
                'user_id' => $userId,
                'channel_id' => $channelId,
            ]);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('checkMovieSubscription failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function checkWebseriesSubscription(Request $request)
    {
        try {
            $id = $request->query('id');
            $userId = $request->query('user_id');
            $channelId = $request->query('channel_id');
            $field = $request->query('field', '_id');
            
            $url = $this->baseUrl . '/nodeapi/rest-api/v130/webseries/details';
            $res = $this->client()->get($url, compact('id', 'field', 'userId', 'channelId'));
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('checkWebseriesSubscription failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function movies(Request $request)
    {
        try {
            $res = $this->client()->get($this->baseUrl . '/nodeapi/rest-api/v130/movies');
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('movies failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function movieById(Request $request)
    {
        $vId = $request->query('vId');
        $country = $request->query('country', 'IN');
        try {
            $url = $this->baseUrl . '/nodeapi/rest-api/v130/movies/single-movie';
            $res = $this->client()->get($url, [
                'vId' => $vId,
                'country' => $country,
            ]);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('movieById failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function webseries()
    {
        try {
            $res = $this->client()->get($this->baseUrl . '/nodeapi/rest-api/v130/webseries');
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('webseries failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function webseriesDetails(Request $request)
    {
        $id = $request->query('id');
        $field = $request->query('field', '_id');
        $userId = $request->query('user_id');
        try {
            $url = $this->baseUrl . '/nodeapi/rest-api/v130/webseries/details';
            $res = $this->client()->get($url, [
                'id' => $id,
                'field' => $field,
                'user_id' => $userId,
                'channel_id' => $channelId,
            ]);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('webseriesDetails failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function seasonsByWebseries(Request $request)
    {
        $webSeriesId = $request->query('webSeriesId');
        try {
            $url = $this->baseUrl . '/nodeapi/rest-api/v130/webseries/webSeries/seasons';
            $res = $this->client()->get($url, [
                'field' => 'webSeries',
                'webSeriesId' => $webSeriesId,
            ]);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('seasonsByWebseries failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }

    public function episodesBySeason($seasonId)
    {
        try {
            $url = $this->baseUrl . "/nodeapi/rest-api/v130/webseries/seasons/{$seasonId}/episodes";
            $res = $this->client()->get($url);
            return response()->json($res->json(), $res->status());
        } catch (\Throwable $e) {
            Log::error('episodesBySeason failed: '.$e->getMessage());
            return response()->json(['error' => 'upstream failed'], 502);
        }
    }
}
