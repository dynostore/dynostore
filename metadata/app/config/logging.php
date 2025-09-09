<?php

use Monolog\Handler\StreamHandler;

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'csv', 'stdout'],
            'ignore_exceptions' => false,
        ],

        // CSV daily file (from earlier)
        'csv' => [
            'driver' => 'daily',
            'path' => storage_path('logs/dynostore.csv.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days'  => env('LOG_DAYS', 14),
            'tap'   => [App\Logging\CustomizeCsvFormatter::class],
        ],

        // Normal single file (optional)
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        // Console / stdout (shows on screen & great for Docker)
        'stdout' => [
            'driver'  => 'monolog',
            'level'   => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'with'    => ['stream' => 'php://stdout'],
        ],
    ],
];

