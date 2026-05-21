<?php

declare(strict_types=1);

use LaravelMailbox\Support\EmailHeaderParser;
use LaravelMailbox\Support\EmailPreviewBuilder;

it('wraps plain text for html preview document', function (): void {
    $document = EmailPreviewBuilder::document(null, 'Hello world');

    expect($document)->toContain('Hello world')
        ->and($document)->toContain('<!DOCTYPE html>')
        ->and($document)->toContain('<body>');
});

it('parses mime headers from raw source', function (): void {
    $raw = <<<'RAW'
From: Test <test@example.com>
To: user@example.com
Subject: Hello
MIME-Version: 1.0
Content-Type: text/plain; charset=utf-8

Body here
RAW;

    $headers = EmailHeaderParser::parse($raw);

    expect($headers)->toHaveKeys(['From', 'To', 'Subject', 'MIME-Version', 'Content-Type'])
        ->and($headers['Subject'])->toBe('Hello');
});
