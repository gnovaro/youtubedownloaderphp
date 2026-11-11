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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'ytdlp' => [
        // Absolute path recommended in production: the web server user may have a different PATH.
        'binary' => env('YTDLP_BINARY', 'yt-dlp'),
        // Optional path to ffmpeg (binary or directory); empty uses ffmpeg from PATH.
        'ffmpeg_location' => env('FFMPEG_LOCATION'),
        'format' => env('YTDLP_FORMAT', 'bestaudio[ext=m4a]/bestaudio'),
        'timeout' => (int) env('YTDLP_TIMEOUT', 300),
    ],

];
