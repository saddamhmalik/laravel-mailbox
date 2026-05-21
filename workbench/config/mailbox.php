<?php

declare(strict_types=1);

return [

    'enabled' => true,

    'route' => [
        'prefix' => 'mailbox',
        'middleware' => ['web', 'mailbox'],
    ],

    'storage' => [
        'driver' => env('MAILBOX_STORAGE_DRIVER', 'database'),
        'max_emails' => 1000,
    ],

    'local_only' => false,

    'ui' => [
        'title' => 'Mailbox',
        'poll_interval_ms' => 3000,
        'per_page' => 25,
    ],

];
