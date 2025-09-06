<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NodeApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {

        $this->baseUrl = config('services.nodeapi.url');
        $this->apiKey = config('services.nodeapi.key');
    }

//    Movies
    public function postMovie($data, $file)
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey
        ])->attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->asMultipart()->post("{$this->baseUrl}/movies", [
            ['name' => 'title', 'contents' => $data['title']],
            ['name' => 'genre', 'contents' => $data['genre']],
        ]);
    }

    public function getAllMovies()
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey
        ])->get("{$this->baseUrl}/movies");
    }


    public function getMovieById($id, $fieldKey)
    {
        return Http::withHeaders([
            'api-key' => $this->apiKey
        ])->get("{$this->baseUrl}/movies/{$id}", [
            'fieldKey' => $fieldKey
        ]);
    }



    //Webseries

    //Genre
    // Get all genres with pagination (cursor-based)
    public function getAllGenres($cursor = null, $direction = 'next', $limit = 10, $sortBy = '_id')
    {
        try {
            $url = "{$this->baseUrl}/genres/all";
            $params = [
                'cursor' => $cursor,
                'direction' => $direction,
                'limit' => $limit,
                'sortBy' => $sortBy
            ];

            $response = Http::withHeaders([
                'api-key' => $this->apiKey
            ])->get($url, $params);
                return $response;

            throw new \Exception('Failed to fetch genres from Node.js API');
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }




}
