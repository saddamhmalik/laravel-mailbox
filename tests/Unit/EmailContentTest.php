<?php

declare(strict_types=1);

use LaravelMailbox\Models\MailboxEmail;
use LaravelMailbox\Support\EmailContent;

it('returns plain text body for plain emails', function (): void {
    $email = new MailboxEmail([
        'text_body' => 'Hello plain',
        'headers' => ['Content-Type' => 'text/plain; charset=utf-8'],
    ]);

    expect(EmailContent::plainText($email))->toBe('Hello plain')
        ->and(EmailContent::contentType($email))->toContain('text/plain')
        ->and(EmailContent::tabHint('text', $email))->toBe('text/plain body');
});

it('strips html when only html part exists', function (): void {
    $email = new MailboxEmail([
        'html_body' => '<p>Hello <strong>HTML</strong></p>',
        'headers' => ['Content-Type' => 'text/html; charset=utf-8'],
    ]);

    expect(EmailContent::plainText($email))->toBe('Hello HTML')
        ->and(EmailContent::tabHint('text', $email))->toBe('HTML stripped to text')
        ->and(EmailContent::tabHint('html', $email))->toBe('Rendered HTML');
});
