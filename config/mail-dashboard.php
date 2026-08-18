<?php

// config for Copain/LaravelMailDashboard
return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | When disabled, outgoing emails are not captured and every dashboard
    | route returns a 404. You most likely only want this dashboard
    | available in your local environment.
    |
    */
    'enabled' => env('MAIL_DASHBOARD_ENABLED', env('APP_ENV', 'production') === 'local'),

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    | The URI path where the dashboard will be accessible from.
    |
    */
    'path' => env('MAIL_DASHBOARD_PATH', 'mail-dashboard'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware applied to every dashboard route. Add your own auth
    | middleware here if you expose the dashboard outside of local dev.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Captured emails are stored as one JSON file each. By default they are
    | written to storage/mail-dashboard on the local filesystem. Point
    | "disk" at any disk from config/filesystems.php (s3, r2, ...) to keep
    | control of where your emails live — required on ephemeral platforms
    | such as Laravel Cloud, where the local filesystem is not persistent.
    |
    */
    'storage' => [
        'disk' => env('MAIL_DASHBOARD_DISK'),
        'path' => env('MAIL_DASHBOARD_STORAGE_PATH', 'mail-dashboard'),
    ],
];
