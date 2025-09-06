#!/bin/bash

# Start Laravel Queue Worker for Cloudflare Upload Jobs
echo "Starting Laravel Queue Worker for video uploads..."

# Run queue worker with optimized settings
php artisan queue:work --queue=uploads,default --timeout=3600 --memory=512 --tries=3 --delay=5

echo "Queue worker started. Press Ctrl+C to stop."
