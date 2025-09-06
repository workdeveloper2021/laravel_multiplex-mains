<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'nodeapi' => [
        'url' => env('NODE_API_URL', 'http://localhost:3000/nodeapi/rest-api/v130'),
        'key' => env('NODE_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'cloudflare' => [
        'account_id'  => env('CLOUDFLARE_ACCOUNT_ID'),
        'api_token'   => env('CLOUDFLARE_API_TOKEN'),
        // Defaults for Stream
        'require_signed_urls' => env('CF_STREAM_REQUIRE_SIGNED_URLS', true),
        'thumbnail_pct'       => env('CF_STREAM_THUMBNAIL_PCT', 0.1),
        'max_duration_sec'    => env('CF_STREAM_MAX_DURATION', 0), // 0 = no limit
        'default_chunk_mb'    => env('CF_STREAM_CHUNK_MB', 64),
    ],

    // Multiplex upstream API (Node)
    'multiplex' => [
        'base_url' => env('MULTIPLEX_BASE_URL', 'https://multiplexplay.com'),
        'api_key'  => env('MULTIPLEX_API_KEY', ''),
    ],

];
