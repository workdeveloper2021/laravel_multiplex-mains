<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    */
    
    'project_id' => env('FCM_PROJECT_ID'),
    'credentials_path' => env('FCM_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
    'batch_size' => 500, // FCM batch limit
    
    // Default notification settings
    'defaults' => [
        'icon' => '/images/app-icon.png',
        'click_action' => '/',
        'sound' => 'default',
    ]
];
