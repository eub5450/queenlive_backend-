<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'agora' => [
            'driver' => 'single',
            'path' => storage_path('logs/agora.log'),
            'level' => 'debug',
            ],
         // Add your custom ranking log
          
            'cornjob' => [
                'driver' => 'daily',
                'path' => storage_path('logs/cornjob.log'),
                'level' => 'info',
                'days' => 1, // keep only 1 day
            ],
            'ranking_api' => [
                'driver' => 'daily',
                'path' => storage_path('logs/ranking_api.log'),
                'level' => 'info',
                'days' => 1, // keep only 1 day
            ],
            'cache_clear' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cache_clear.log'),
            'level' => 'info',
            'days' => 1,
        ],'redis_slow' => [
        'driver' => 'daily',
        'path' => storage_path('logs/redis_slow.log'),
        'level' => 'warning',
        'days' => 7,
         ],
    
        'redis_error' => [
        'driver' => 'daily',
        'path' => storage_path('logs/redis_error.log'),
        'level' => 'error',
        'days' => 30,
        ],'backup' => [
        'driver' => 'daily',
        'path' => storage_path('logs/backup.log'),
        'level' => 'error',
        'days' => 30,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
