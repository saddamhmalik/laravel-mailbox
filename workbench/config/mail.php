<?php

declare(strict_types=1);

return [
    'default' => env('MAIL_MAILER', 'mailbox'),
    'mailers' => [
        'mailbox' => [
            'transport' => 'mailbox',
        ],
        'array' => [
            'transport' => 'array',
        ],
    ],
    'from' => [
        'address' => 'workbench@mailbox.test',
        'name' => 'Mailbox Workbench',
    ],
];
