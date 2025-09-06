<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Environment Test ===\n";
echo "CLOUDFLARE_ACCOUNT_ID: " . (env('CLOUDFLARE_ACCOUNT_ID') ?: 'NOT SET') . "\n";
echo "CLOUDFLARE_API_TOKEN: " . (env('CLOUDFLARE_API_TOKEN') ? 'SET (' . strlen(env('CLOUDFLARE_API_TOKEN')) . ' chars)' : 'NOT SET') . "\n";

echo "\n=== Service Test ===\n";
try {
    $service = app(\App\Services\CloudflareStreamService::class);
    echo "✅ CloudflareStreamService created successfully\n";
    
    // Test credentials access
    $reflection = new ReflectionClass($service);
    $accountIdProperty = $reflection->getProperty('accountId');
    $accountIdProperty->setAccessible(true);
    $accountId = $accountIdProperty->getValue($service);
    
    $apiTokenProperty = $reflection->getProperty('apiToken');
    $apiTokenProperty->setAccessible(true);
    $apiToken = $apiTokenProperty->getValue($service);
    
    echo "Service accountId: " . ($accountId ?: 'EMPTY') . "\n";
    echo "Service apiToken: " . ($apiToken ? 'SET (' . strlen($apiToken) . ' chars)' : 'EMPTY') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== .env File Check ===\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ .env file exists\n";
    $envContent = file_get_contents($envPath);
    if (strpos($envContent, 'CLOUDFLARE_ACCOUNT_ID') !== false) {
        echo "✅ CLOUDFLARE_ACCOUNT_ID found in .env\n";
    } else {
        echo "❌ CLOUDFLARE_ACCOUNT_ID NOT found in .env\n";
    }
    if (strpos($envContent, 'CLOUDFLARE_API_TOKEN') !== false) {
        echo "✅ CLOUDFLARE_API_TOKEN found in .env\n";
    } else {
        echo "❌ CLOUDFLARE_API_TOKEN NOT found in .env\n";
    }
} else {
    echo "❌ .env file not found\n";
}
