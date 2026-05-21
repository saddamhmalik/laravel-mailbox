<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;

it('supports pest mailbox expectations', function (): void {
    Mail::raw('Hello', fn ($message) => $message->to('john@example.com')->subject('Welcome'));

    expectMailbox()
        ->to('john@example.com')
        ->withSubject('Welcome')
        ->assert();
});
