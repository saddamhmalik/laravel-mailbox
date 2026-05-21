<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use LaravelMailbox\Facades\Mailbox;
use LaravelMailbox\Models\MailboxEmail;

it('captures emails sent through the mailbox mailer', function (): void {
    Mail::raw('Hello mailbox', function ($message): void {
        $message->to('test@example.com')
            ->subject('Test Subject');
    });

    Mailbox::assertSentCount(1);
    Mailbox::assertSent(fn (MailboxEmail $email): bool => $email->hasTo('test@example.com'));

    expect(MailboxEmail::query()->count())->toBe(1);
});

it('stores html and text bodies', function (): void {
    Mail::send([], [], function ($message): void {
        $message->to('user@example.com')
            ->subject('Welcome')
            ->html('<p>Hello</p>')
            ->text('Hello');
    });

    $email = MailboxEmail::query()->first();

    expect($email)->not->toBeNull()
        ->and($email->subject)->toBe('Welcome')
        ->and($email->html_body)->toContain('Hello')
        ->and($email->text_body)->toBe('Hello');
});
