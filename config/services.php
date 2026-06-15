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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'dataforseo' => [
        'login'    => env('DATAFORSEO_LOGIN', ''),
        'password' => env('DATAFORSEO_PASSWORD', ''),
    ],

    'google_pagespeed' => [
        'api_key' => env('GOOGLE_PAGESPEED_API_KEY'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'google_search_console' => [
        'client_id'     => env('GSC_CLIENT_ID', env('GOOGLE_CLIENT_ID')),
        'client_secret' => env('GSC_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET')),
        'redirect_uri'  => env('GSC_REDIRECT_URI', '/api/search-console/callback'),
        'scopes'        => [
            'https://www.googleapis.com/auth/webmasters.readonly',
        ],
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY', ''),
        'api_url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    'openpagerank' => [
        'api_key' => env('OPENPAGERANK_API_KEY', ''),
    ],

];
