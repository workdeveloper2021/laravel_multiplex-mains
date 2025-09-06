<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\FrontendPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MovieController;
use \App\Http\Controllers\WebseriesController;
use \App\Http\Controllers\PlanController;
use \App\Http\Controllers\ChannelController;
use \App\Http\Controllers\GenreController;
use \App\Http\Controllers\BannerController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\TransactionLogController;
use \App\Http\Controllers\NotificationController;
use \App\Http\Controllers\HomeController;
use \App\Http\Controllers\FrontMovieController;
use \App\Http\Controllers\HomeBannerController;
use \App\Http\Controllers\SeasonsController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProfileController;

//Route::get('/', [HomeController::class, 'HomeBanner'])->name('home');


//LoginAuth
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('login', [AuthController::class, 'login']);
Route::get('userData/index.html', function () {
    return view('delete-google');
})->middleware('guest');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('/delete-google', [GoogleController::class, 'deleteByEmail'])->name('delete-data');

Route::get('user-login', [AuthController::class, 'showUserLoginForm'])->name('user-login');
Route::post('user-login', [AuthController::class, 'userLogin'])->name('user-login.post');
Route::get('verify', [AuthController::class, 'showOtpForm'])->name('verify');
Route::post('user-send-otp', [AuthController::class, 'sendOtpToUser'])->name('user-send-otp');
Route::post('user-verify-otp', [AuthController::class, 'verifyOtp'])->name('user-verify-otp');
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::get('register-detail', [AuthController::class, 'showRegisterDetailForm'])->name('register.detail');
Route::post('register-detail', [AuthController::class, 'storeRegisterDetail'])->name('register.detail.save');
Route::get('register-details', [AuthController::class, 'showRegisterDetailsForm'])->name('register.details');
Route::post('register-details', [AuthController::class, 'storeRegisterDetails'])->name('register.details.save');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::controller(ProfileController::class)->prefix('myprofile')->group(function () {
    Route::get('/', 'show')->name('show.profile');
    Route::get('/edit', 'edit')->name('edit.profile');
    Route::post('/update', 'update')->name('update.profile');
});


//Frontend Page Routes
Route::get('about', [FrontendPageController::class, 'about'])->name('about');
Route::get('tc', [FrontendPageController::class, 'tc'])->name('tc');
Route::get('policy', [FrontendPageController::class, 'policy'])->name('policy');
Route::get('help', [FrontendPageController::class, 'help'])->name('help');
Route::get('contact', [FrontendPageController::class, 'contact'])->name('contact');
Route::get('user-data', [FrontendPageController::class, 'delUserData'])->name('user-data');

//Route::get('user-login', [FrontendPageController::class, 'userLogin'])->name('user-login');
//Route::get('channel-login', [FrontendPageController::class, 'channelLogin'])->name('channel-login');
//Route::get('register', [FrontendPageController::class, 'RegisterUser'])->name('register');
//Route::get('userLogin', [FrontendPageController::class, 'about'])->name('about');

Route::get('/api/fetch-home-data', [FrontendPageController::class, 'getBanners']);
// Route::get('/', function () {
//         return view('welcome');
// });

Route::get('/', [FrontendPageController::class, 'home'])->name('home');

// Secure Video Routes
use App\Http\Controllers\SecureVideoController;
use App\Http\Controllers\VideoSessionController;
use App\Http\Controllers\ApiProxyController;
use App\Http\Controllers\CloudflareWebhookController;
use App\Http\Controllers\EpisodeController;

Route::get('/api/secure-video', [SecureVideoController::class, 'serveSecureVideo'])->name('video.secure');
Route::get('/api/video-proxy', [SecureVideoController::class, 'proxyVideoStream'])->name('video.proxy');
Route::get('/api/video-manifest', [SecureVideoController::class, 'getVideoManifest'])->name('video.manifest');

// OTT API proxy routes (hide api-key from browser)
Route::prefix('/api/ott')->group(function () {
    Route::get('/home', [ApiProxyController::class, 'homeContent']); // ?country=IN
    Route::get('/movies', [ApiProxyController::class, 'movies']);
    Route::get('/movie', [ApiProxyController::class, 'movieById']); // ?vId=...&country=IN
    Route::get('/webseries', [ApiProxyController::class, 'webseries']);
    Route::get('/webseries/details', [ApiProxyController::class, 'webseriesDetails']);
    Route::get('/webseries/seasons', [ApiProxyController::class, 'seasonsByWebseries']);
    Route::get('/webseries/seasons/{seasonId}/episodes', [ApiProxyController::class, 'episodesBySeason']);

    // Subscription checks before play
    Route::get('/check-movie', [ApiProxyController::class, 'checkMovieSubscription']); // ?vId=...&user_id=...&channel_id=...&country=IN
    Route::get('/check-webseries', [ApiProxyController::class, 'checkWebseriesSubscription']); // ?id=...&user_id=...&channel_id=...&field=_id
});
Route::post('/cf/stream/webhook', [CloudflareWebhookController::class, 'handle'])->name('cf.stream.webhook');

// Video Session Management Routes
Route::post('/api/check-video-session', [VideoSessionController::class, 'checkVideoSession']);
Route::post('/api/video-heartbeat', [VideoSessionController::class, 'videoHeartbeat']);
Route::post('/api/check-video-conflict', [VideoSessionController::class, 'checkVideoConflict']);
Route::post('/api/force-video-session', [VideoSessionController::class, 'forceVideoSession']);
Route::post('/api/end-video-session', [VideoSessionController::class, 'endVideoSession']);
Route::post('/api/add-to-watchlist', [VideoSessionController::class, 'addToWatchlist'])->middleware('auth');
Route::get('/api/active-sessions', [VideoSessionController::class, 'getActiveSessions'])->middleware('auth');

//Route::get('/', function () {
//})->name('home');


Route::get('/front-movies', [FrontMovieController::class, 'index'])->name('frontmovies.index');



Route::middleware(['auth', 'complete.registration'])->group(function () {

    // Common dashboard route
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    // Movies
    Route::resource('movies', MovieController::class)
        ->only(['index', 'store', 'create', 'edit', 'update', 'destroy'])
        ->parameters(['movies' => 'movie:_id']);


    Route::resource('tlogs', TransactionLogController::class);
    // Route::resource('movies', MovieController::class)
    //     ->only(['index', 'store', 'create', 'edit', 'update', 'destroy', 'show'])
    //     ->parameters(['movies' => 'movie:slug']);
    // Admin-only routes


    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [UserController::class, 'update'])->name('profile.update');


    // check: likely wrong controller
    // Web Series
    //    Route::resource('webseries', WebseriesController::class);
    //    Route::get('webseries/{webseries}/createSeason', [WebseriesController::class, 'createSeason'])->name('webseries.createSeson');
    //    Route::post('webseries/{webseries}/storeSeason', [WebseriesController::class, 'storeSeason'])->name('webseries.storeSeason');
    //     Route::resource('seasons', SeasonsController::class);
    //     Route::resource('episodes', WebseriesController::class);
    Route::prefix('content')->name('content.')->group(function () {
        // Movies Management
        Route::resource('movies', MovieController::class)
            ->only(['index', 'store', 'create', 'edit', 'update', 'destroy'])
            ->parameters(['movies' => 'movie:_id']);
        Route::get('movies/{id}/upload-video', [MovieController::class, 'uploadVideo'])->name('movies.upload-video');
        Route::post('movies/{id}/store-video', [MovieController::class, 'storeVideo'])->name('movies.store-video');
        Route::get('movies/upload-progress', [MovieController::class, 'getUploadProgress'])->name('movies.upload-progress');

        // Direct Cloudflare Upload Routes (Optimized)
        Route::post('movies/{id}/generate-upload-url', [MovieController::class, 'generateUploadUrl'])->name('movies.generate-upload-url');
        Route::post('movies/{id}/upload-complete', [MovieController::class, 'handleUploadComplete'])->name('movies.upload-complete');

        // Web Series Management
        Route::resource('webseries', WebseriesController::class);
        Route::get('webseries/{webseries}/editWebseries', [WebseriesController::class, 'editWebseries'])->name('webseries.editWebseries');

        // Season Management (nested under webseries)
        Route::get('webseries/{webseries}/seasons', [SeasonsController::class, 'index'])->name('webseries.seasons.index');
        Route::get('webseries/{webseries}/seasons/create', [SeasonsController::class, 'create'])->name('webseries.seasons.create');
        Route::post('webseries/{webseries}/seasons', [SeasonsController::class, 'store'])->name('webseries.seasons.store');
        Route::get('seasons/{season}/edit', [SeasonsController::class, 'edit'])->name('seasons.edit');
        Route::put('seasons/{season}', [SeasonsController::class, 'update'])->name('seasons.update');
        Route::delete('seasons/{season}', [SeasonsController::class, 'destroy'])->name('seasons.destroy');

        // Episode Management (nested under seasons)
        Route::get('seasons/{season}/episodes', [EpisodeController::class, 'index'])->name('seasons.episodes.index');
        Route::get('seasons/{season}/episodes/create', [EpisodeController::class, 'create'])->name('seasons.episodes.create');
        Route::post('seasons/{season}/episodes', [EpisodeController::class, 'store'])->name('seasons.episodes.store');
        Route::get('episodes/{episode}/edit', [EpisodeController::class, 'edit'])->name('episodes.edit');
        Route::put('episodes/{episode}', [EpisodeController::class, 'update'])->name('episodes.update');
        Route::delete('episodes/{episode}', [EpisodeController::class, 'destroy'])->name('episodes.destroy');
        Route::get('episodes/upload-progress', [EpisodeController::class, 'getUploadProgress'])->name('episodes.upload-progress');

        // Genre Management
        Route::resource('genre', GenreController::class);
    });

    // Country Management
    Route::resource('countries', CountryController::class);

    // Package Plan
    Route::resource('plan', PlanController::class);

    // Channel Management
    Route::prefix('channels')->name('channels.')->group(function () {
        Route::get('approve', [ChannelController::class, 'approve'])->name('approve');
        Route::get('pending-videos', [ChannelController::class, 'pendingVideos'])->name('pendingVideos');
        Route::get('rejected-videos', [ChannelController::class, 'rejectedVideos'])->name('rejectedVideos');
        Route::get('blocked-videos', [ChannelController::class, 'blockedVideos'])->name('blockedVideos');
        Route::get('videos', [ChannelController::class, 'allVideos'])->name('allVideos');
    });
    Route::resource('channels', ChannelController::class)->except(['create', 'store']);

    // Genre
    Route::resource('genre', GenreController::class);

    // Banners
    Route::resource('banner', BannerController::class);
    Route::resource('home-banner', HomeBannerController::class);

    // Users
    Route::get('users/get-plans', [UserController::class, 'getPlans'])->name('users.get-plans');
    Route::get('users/get-channels', [UserController::class, 'getChannels'])->name('users.get-channels');
    Route::post('users/assign-plan', [UserController::class, 'assignPlan'])->name('users.assign-plan');
    Route::post('users/assign-payment', [UserController::class, 'assignPayment'])->name('users.assign-payment');
    Route::resource('users', UserController::class);

    // Transaction Logs
    // Route::resource('tlogs', TransactionLogController::class);

    // Notifications
    Route::resource('notify', NotificationController::class);






    //Frontend VideoPlayer
    // API used by your Blade/JS
    Route::prefix('api/ott')->group(function () {
        Route::get('/home', [FrontMovieController::class, 'homeData'])->name('api.ott.home');
        Route::get('/check-movie', [FrontMovieController::class, 'movie'])->name('api.ott.check-movie');
        Route::get('/check-webseries', [FrontMovieController::class, 'webseries'])->name('api.ott.check-webseries');
        // Route::get('/check', [FrontMovieController::class, 'check'])->name('api.ott.check'); // optional
    });

    Route::get('/api/video-manifest', [FrontMovieController::class, 'videoManifest'])->name('api.video.manifest');
});



// Channel-only routes












































//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Route::get('/', function () {
////    return view('welcome');
//    try {
//        $users = \App\Models\User::all(); // Fetch all users
//        dd($users);
//        return response()->json([
//            'status' => 'success',
//            'data' => $users
//        ]);
//    } catch (\Exception $e) {
//        return response()->json([
//            'status' => 'error',
//            'message' => 'DB Connection Failed',
//            'error' => $e->getMessage()
//        ]);
//    }
//});

//Route::get('/', function () {
//    try {
//        $model = new \App\Models\User();
//
//        Log::info('Collection being used: ' . $model->getTable());
//        dd((new \App\Models\User()));
//
//        // Insert test data
////        \App\Models\MongoUser::create([
////            'name' => 'Dhanesh Joshi',
////            'email' => 'dhanesh@example.com',
////        ]);
//        return 'Check logs!';
//        // Fetch all data
////        $data = \App\Models\MongoUser::all();
//
////        return response()->json([
////            'status' => 'MongoDB is connected 🎉',
////            'data' => $data
////        ]);
//
//    } catch (\Exception $e) {
//        return response()->json([
//            'status' => 'MongoDB connection failed ❌',
//            'error' => $e->getMessage()
//        ]);
//    }
//});
