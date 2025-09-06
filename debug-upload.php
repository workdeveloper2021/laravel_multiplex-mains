<?php

// Quick debug script to test upload configuration

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Testing Upload Configuration...\n\n";

// Test 1: Check environment variables
echo "1. Environment Variables:\n";
echo "   CLOUDFLARE_ACCOUNT_ID: " . (config('services.cloudflare.CLOUDFLARE_ACCOUNT_ID') ? 'SET ✅' : 'NOT SET ❌') . "\n";
echo "   CLOUDFLARE_API_TOKEN: " . (config('services.cloudflare.CLOUDFLARE_API_TOKEN') ? 'SET ✅' : 'NOT SET ❌') . "\n";

// Test 2: Check database connection
echo "\n2. Database Connection:\n";
try {
    $movie = \App\Models\Movie::first();
    if ($movie) {
        echo "   MongoDB Connection: ✅\n";
        echo "   Sample Movie ID: " . $movie->_id . "\n";
        echo "   Sample Movie Title: " . $movie->title . "\n";
    } else {
        echo "   No movies found in database ❌\n";
    }
} catch (Exception $e) {
    echo "   Database Error: " . $e->getMessage() . " ❌\n";
}

// Test 3: Test ObjectId helper
echo "\n3. ObjectId Helper Test:\n";
try {
    if ($movie) {
        $movieController = new \App\Http\Controllers\MovieController();
        $reflection = new ReflectionClass($movieController);
        $method = $reflection->getMethod('findMovieById');
        $method->setAccessible(true);
        
        $foundMovie = $method->invoke($movieController, (string)$movie->_id);
        echo "   ObjectId Lookup: ✅ (Found: " . $foundMovie->title . ")\n";
    }
} catch (Exception $e) {
    echo "   ObjectId Helper Error: " . $e->getMessage() . " ❌\n";
}

// Test 4: Test CloudflareStreamService
echo "\n4. CloudflareStreamService Test:\n";
try {
    $cloudflareService = app(\App\Services\CloudflareStreamService::class);
    
    $testMetadata = [
        'name' => 'test-video.mp4',
        'size' => 10 * 1024 * 1024, // 10MB
        'type' => 'video/mp4'
    ];
    
    $result = $cloudflareService->generateSignedUploadUrl($testMetadata);
    
    if ($result['success']) {
        echo "   Signed URL Generation: ✅\n";
        echo "   Stream ID: " . $result['stream_id'] . "\n";
        echo "   Upload URL: " . substr($result['upload_url'], 0, 60) . "...\n";
    } else {
        echo "   Signed URL Generation: ❌ - " . $result['error'] . "\n";
    }
    
} catch (Exception $e) {
    echo "   CloudflareStreamService Error: " . $e->getMessage() . " ❌\n";
}

// Test 5: Test routes
echo "\n5. Routes Test:\n";
try {
    $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function($route) {
        return str_contains($route->uri(), 'generate-upload-url') || str_contains($route->uri(), 'upload-complete');
    });
    
    echo "   Upload Routes Found: " . $routes->count() . " ✅\n";
    foreach ($routes as $route) {
        echo "   - " . $route->methods()[0] . " " . $route->uri() . "\n";
    }
} catch (Exception $e) {
    echo "   Routes Test Error: " . $e->getMessage() . " ❌\n";
}

echo "\n🎯 Test Complete!\n";

if (config('services.cloudflare.CLOUDFLARE_ACCOUNT_ID') && config('services.cloudflare.CLOUDFLARE_API_TOKEN')) {
    echo "\n✅ Ready for testing! Go to: /content/movies/{movie_id}/upload-video\n";
} else {
    echo "\n⚠️  Please set CLOUDFLARE_ACCOUNT_ID and CLOUDFLARE_API_TOKEN in your .env file\n";
}

echo "\n📝 Debug logs will be in: storage/logs/laravel.log\n";
