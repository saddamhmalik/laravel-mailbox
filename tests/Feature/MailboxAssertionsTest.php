<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use LaravelMailbox\Facades\Mailbox;

it('asserts nothing was sent', function (): void {
    Mailbox::assertNothingSent();
});

it('asserts sent with callback', function (): void {
    Mail::raw('Body', fn ($message) => $message->to('john@example.com')->subject('Welcome'));

    Mailbox::assertSent(fn ($email) => $email->hasTo('john@example.com') && $email->hasSubject('Welcome'));
});

it('flushes captured emails', function (): void {
    Mail::raw('Body', fn ($message) => $message->to('a@example.com'));

    expect(Mailbox::count())->toBe(1);

    Mailbox::flush();

    Mailbox::assertNothingSent();
});
