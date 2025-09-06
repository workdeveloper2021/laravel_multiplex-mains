<?php

namespace App\Http\Controllers;

use App\Models\Episodes;
use App\Models\HomeBanner;
use App\Models\Movie;
use App\Models\WebSeries;
use Spatie\FlareClient\Http\Client;

class FrontendPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function tc()
    {
        return view('terms');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function policy()
    {
        return view('privacy');
    }

    /**
     * Display the specified resource.
     */
    public function help()
    {
        return view('help');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Update the specified resource in storage.
     */
    public function delUserData()
    {
        return view('remove_data');
    }

    /**
     * Remove the specified resource from storage.
    */

    public function getBanners()
    {
        $banners = HomeBanner::all();
        return response()->json([
            'success' => true,
            'message' => 'Banners fetched successfully',
            'data' => $banners
        ]);
    }

    public function home()
    {
        $banners = HomeBanner::all();
        $latestMovies = Movie::orderBy('created_at', 'desc')->select('thumbnail_url', 'poster_url')->limit(10)->get();
        $latestEpisodes = WebSeries::orderBy('created_at', 'desc')->select('thumbnail_url', 'poster_url', 'image_url')->limit(10)->get();
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get('https://multiplexplay.com/nodeapi/rest-api/v130/home_content_for_android', [
                'headers' => [
                    'api-key' => 'ec8590cb04e0e37c6706ab6c',
                ],
            ]);

            $body = $response->getBody();
            $data = json_decode($body, true);

            // Extract specific section
            $dynamicData = $data['features_genre_and_movie'] ?? [];
            // Transform banners for video carousel
            $videoCarouselBanners = $this->transformBannersForCarousel($banners);

            // Transform dynamic data for content sections
            $genres = $this->transformGenresForDisplay($dynamicData);

            // Get featured video for hero section
            $featured_video = $this->getFeaturedVideo();

            return view('welcome', compact('banners', 'latestMovies', 'latestEpisodes', 'dynamicData', 'genres', 'featured_video'));

        } catch (\Exception $e) {
            // Handle API errors gracefully - use local database data
            $videoCarouselBanners = $this->getSampleBanners();
            $genres = $this->getSampleGenres();
            
            // Get featured video for error case too
            $featured_video = $this->getFeaturedVideo();
            
            // Create fallback dynamicData from local database
            $dynamicData = $this->getLocalMovieData();

            return view('welcome', [
                'banners' => $videoCarouselBanners,
                'genres' => $genres,
                'featured_video' => $featured_video,
                'dynamicData' => $dynamicData,
                'latestMovies' => $latestMovies,
                'latestEpisodes' => $latestEpisodes,
                'error' => 'Using local data: API request failed'
            ]);
        }
    }

    private function transformBannersForCarousel($banners)
    {
        return $banners->map(function ($banner) {
            return [
                'title' => $banner->title ?? 'Premium Entertainment',
                'description' => $banner->description ?? 'Experience the best in streaming entertainment.',
                'video_url' => $banner->video_url ?? null, // Add video_url field to HomeBanner model
                'image_url' => $banner->image_url ?? $banner->banner_url ?? '/images/default-banner.jpg',
                'cta_url' => $banner->cta_url ?? '/register',
                'cta_text' => $banner->cta_text ?? 'Watch Now'
            ];
        })->toArray();
    }

    private function transformGenresForDisplay($dynamicData)
    {
        $genres = [];

        foreach ($dynamicData as $genre) {
            if (isset($genre['genre_name']) && isset($genre['movies'])) {
                $genres[] = [
                    'name' => $genre['genre_name'],
                    'content' => collect($genre['movies'])->map(function ($movie) {
                        return [
                            'id' => $movie['_id'] ?? $movie['id'] ?? '',
                            'title' => $movie['title'] ?? 'Untitled',
                            'description' => $movie['description'] ?? 'No description available.',
                            'thumbnail' => $movie['thumbnail_url'] ?? $movie['poster_url'] ?? '/images/default-movie.jpg'
                        ];
                    })->take(10)->toArray()
                ];
            }
        }

        return $genres;
    }

    private function getSampleBanners()
    {
        return [
            [
                'title' => 'Premium Entertainment',
                'description' => 'Experience the best in streaming entertainment with 4K quality.',
                'video_url' => 'https://multiplexplay.com/storage/sample/video1.m3u8',
                'image_url' => '/images/hero1.jpg',
                'cta_url' => '/register',
                'cta_text' => 'Start Free Trial'
            ],
            [
                'title' => 'Latest Releases',
                'description' => 'Watch the latest movies and web series before anyone else.',
                'video_url' => null,
                'image_url' => '/images/hero2.jpg',
                'cta_url' => '/movies',
                'cta_text' => 'Browse Movies'
            ]
        ];
    }

    private function getSampleGenres()
    {
        return [
            [
                'name' => 'Action Movies',
                'content' => [
                    [
                        'id' => '1',
                        'title' => 'Quantum Leap',
                        'description' => 'A mind-bending sci-fi adventure through time and space.',
                        'thumbnail' => '/images/movie1.jpg'
                    ]
                ]
            ]
        ];
    }

    private function getFeaturedVideo()
    {
        // Get featured video from database - latest approved movie with video URL
        $featuredMovie = Movie::where('publication', 1)
            ->where('status', 'approve')
            ->whereNotNull('video_url')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($featuredMovie) {
            return [
                'id' => (string) $featuredMovie->_id,
                'title' => $featuredMovie->title,
                'description' => $featuredMovie->description ?? 'Premium streaming content',
                'video_url' => $featuredMovie->video_url ?? $featuredMovie->stream_url,
                'thumbnail' => $featuredMovie->thumbnail_url ?? $featuredMovie->poster_url,
                'is_paid' => $featuredMovie->is_paid ?? 0
            ];
        }

        // Fallback to sample featured video
        return [
            'id' => 'featured_sample',
            'title' => 'MultiplexPlay Exclusive',
            'description' => 'Experience premium entertainment with crystal clear HD quality and secure streaming.',
            'video_url' => 'https://multiplexplay.com/storage/featured/sample.m3u8',
            'thumbnail' => '/images/featured-video.jpg',
            'is_paid' => 0
        ];
    }

    /**
     * Get local movie data when API fails
     */
    private function getLocalMovieData()
    {
        try {
            // Get movies from local database
            $movies = Movie::select('_id', 'title', 'thumbnail_url', 'poster_url', 'genre')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
            // Group movies by genre (simplified)
            $moviesByGenre = $movies->groupBy('genre');
            
            $dynamicData = [];
            
            foreach ($moviesByGenre as $genre => $genreMovies) {
                if (empty($genre)) {
                    $genre = 'Latest Movies';
                }
                
                $dynamicData[] = [
                    'genre_name' => $genre,
                    'movies' => $genreMovies->map(function ($movie) {
                        return [
                            '_id' => (string) $movie->_id,
                            'title' => $movie->title,
                            'poster_url' => $movie->poster_url ?? $movie->thumbnail_url,
                            'thumbnail_url' => $movie->thumbnail_url ?? $movie->poster_url
                        ];
                    })->toArray()
                ];
            }
            
            // If no movies found, create a section with web series
            if (empty($dynamicData)) {
                $webSeries = WebSeries::select('_id', 'title', 'thumbnail_url', 'poster_url', 'image_url')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
                    
                if ($webSeries->count() > 0) {
                    $dynamicData[] = [
                        'genre_name' => 'Web Series',
                        'movies' => $webSeries->map(function ($series) {
                            return [
                                '_id' => (string) $series->_id,
                                'title' => $series->title,
                                'poster_url' => $series->poster_url ?? $series->thumbnail_url ?? $series->image_url,
                                'thumbnail_url' => $series->thumbnail_url ?? $series->poster_url ?? $series->image_url
                            ];
                        })->toArray()
                    ];
                }
            }
            
            return $dynamicData;
            
        } catch (\Exception $e) {
            // Return empty array if database also fails
            return [];
        }
    }
}
