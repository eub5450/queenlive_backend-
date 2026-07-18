<?php

use BeyondCode\LaravelWebSockets\Apps\ConfigAppProvider;
use BeyondCode\LaravelWebSockets\Dashboard\Http\Middleware\Authorize;

return [

    'dashboard' => [
        'port' => env('LARAVEL_WEBSOCKETS_PORT', 6001),
    ],

    'apps' => [
        [
            'id' => env('PUSHER_APP_ID'),
            'name' => env('APP_NAME', 'Laravel'),
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'path' => env('PUSHER_APP_PATH'),
            'capacity' => null,
            'enable_client_messages' => false,
            'enable_statistics' => true,
        ],
    ],

    'app_provider' => ConfigAppProvider::class,

    'allowed_origins' => [
        'queenlive.site',
        'www.queenlive.site',
        'fairylive.online',
        'www.fairylive.online',
        'broadlive.xyz',
        'www.broadlive.xyz',
        'thomasgamecompanyltd.queenlive.site',
        'thomaslivestreamingcompanyltdgame.fairylive.online',
        'thomaslivestreamingcompanyltdgame.broadlive.xyz',
        'localhost',
        '127.0.0.1',
    ],

    'max_request_size_in_kb' => 250,

    'path' => 'laravel-websockets',

    'middleware' => [
        'web',
        Authorize::class,
    ],

    'statistics' => [
        'model' => \BeyondCode\LaravelWebSockets\Statistics\Models\WebSocketsStatisticsEntry::class,
        'logger' => BeyondCode\LaravelWebSockets\Statistics\Logger\HttpStatisticsLogger::class,
        'interval_in_seconds' => 60,
        'delete_statistics_older_than_days' => 60,
        'perform_dns_lookup' => false,
    ],

    'ssl' => [
        'local_cert' => null,
        'local_pk' => null,
        'passphrase' => null,
    ],

    'channel_manager' => \BeyondCode\LaravelWebSockets\WebSockets\Channels\LocalChannelManager::class,

];
