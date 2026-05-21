<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/mailbox');
});

Route::get('/send-test', function () {
    Mail::raw('Hello from Laravel Mailbox workbench!', function ($message): void {
        $message->to('demo@example.com')
            ->subject('Workbench Test Email');
    });

    return redirect('/mailbox')->with('status', 'Test email sent!');
});
