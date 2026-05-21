<?php

declare(strict_types=1);

return [

    'enabled' => env('MAILBOX_ENABLED', true),

    'route' => [
        'prefix' => env('MAILBOX_ROUTE_PREFIX', 'mailbox'),
        'middleware' => ['web', 'mailbox'],
        'domain' => env('MAILBOX_ROUTE_DOMAIN'),
    ],

    'storage' => [
        'driver' => env('MAILBOX_STORAGE_DRIVER', 'database'),
        'connection' => env('MAILBOX_DB_CONNECTION'),
        'table' => 'mailbox_emails',
        'max_emails' => (int) env('MAILBOX_MAX_EMAILS', 1000),
    ],

    'prune' => [
        'enabled' => true,
        'older_than_days' => 7,
        'schedule' => true,
    ],

    'ui' => [
        'title' => 'Mailbox',
        'poll_interval_ms' => 3000,
        'per_page' => 25,
        'dark_mode' => true,
        'use_vite' => env('MAILBOX_USE_VITE', false),
    ],

    'authorization' => null,

    'local_only' => env('MAILBOX_LOCAL_ONLY', true),

];
