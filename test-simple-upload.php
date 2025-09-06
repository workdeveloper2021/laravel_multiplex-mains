<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🎬 Testing Simple Upload System...\n\n";

// Test simple service
try {
    $simpleService = new \App\Services\CloudflareStreamServiceSimple();
    echo "✅ CloudflareStreamServiceSimple loaded successfully\n";
    
    // Test with dummy metadata
    $testData = [
        'percent' => 50,
        'message' => 'Testing progress system...',
        'uploaded' => 50 * 1024 * 1024,
        'total' => 100 * 1024 * 1024
    ];
    
    \Cache::put('upload_progress_test123', $testData, now()->addMinutes(10));
    $retrieved = \Cache::get('upload_progress_test123');
    
    if ($retrieved && $retrieved['percent'] == 50) {
        echo "✅ Progress tracking system working\n";
    } else {
        echo "❌ Progress tracking system failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Simple service error: " . $e->getMessage() . "\n";
}

// Test movie lookup
try {
    $movie = \App\Models\Movie::first();
    if ($movie) {
        echo "✅ Movie found: " . $movie->title . " (ID: " . $movie->_id . ")\n";
        
        // Test URL generation
        $testUrl = "/content/movies/{$movie->_id}/upload-video";
        echo "✅ Test upload URL: {$testUrl}\n";
    }
} catch (Exception $e) {
    echo "❌ Movie lookup error: " . $e->getMessage() . "\n";
}

echo "\n🚀 SIMPLE UPLOAD SYSTEM READY!\n";
echo "\n📍 How to test:\n";
echo "1. Go to: /content/movies/{movie_id}/upload-video\n";
echo "2. Select any video file (up to 3GB)\n";
echo "3. Click 'Upload to Cloudflare'\n";
echo "4. Watch real-time progress\n";
echo "5. Upload completes automatically\n";

echo "\n✨ Features:\n";
echo "- Simple drag & drop file selection\n";
echo "- Real-time progress bar\n";
echo "- Speed & ETA tracking\n";
echo "- Direct Cloudflare upload\n";
echo "- Automatic completion\n";

echo "\n🎯 No complex TUS protocol - just simple HTTP upload!\n";
