<?php

require_once __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$movieId = $argv[1] ?? null;
$localPath = $argv[2] ?? null;
$sessionId = $argv[3] ?? null;

echo "Background upload script started\n";
echo "Movie ID: $movieId\n";
echo "Local Path: $localPath\n";
echo "Session ID: $sessionId\n";

if ($movieId && $localPath && $sessionId) {
    try {
        echo "Creating CloudflareUploadJob...\n";
        $job = new App\Jobs\CloudflareUploadJob($movieId, $localPath, $sessionId);
        
        echo "Starting job execution...\n";
        $job->handle();
        
        echo "Background upload completed successfully\n";
        
    } catch (Exception $e) {
        echo "Background upload failed: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
        
        file_put_contents("storage/logs/background-upload-error.log", 
            date("Y-m-d H:i:s") . " - Error: " . $e->getMessage() . "\n" . 
            "Stack: " . $e->getTraceAsString() . "\n\n", 
            FILE_APPEND
        );
    }
} else {
    echo "Missing arguments - movieId: $movieId, localPath: $localPath, sessionId: $sessionId\n";
}
