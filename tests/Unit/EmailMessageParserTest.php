<?php

declare(strict_types=1);

use LaravelMailbox\Support\EmailMessageParser;
use Symfony\Component\Mime\Email;

it('parses symfony email messages into captured data', function (): void {
    $email = (new Email)
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Parsed')
        ->html('<p>Hi</p>')
        ->text('Hi');

    $data = (new EmailMessageParser)->parse($email, 'mailbox', 'sync');

    expect($data->subject)->toBe('Parsed')
        ->and($data->to[0]['address'])->toBe('recipient@example.com')
        ->and($data->htmlBody)->toContain('Hi')
        ->and($data->mailer)->toBe('mailbox')
        ->and($data->queue)->toBe('sync');
});
