<?php

declare(strict_types=1);

namespace LaravelMailbox\Support;

use LaravelMailbox\Models\MailboxEmail;

final class EmailContent
{
    public static function plainText(MailboxEmail $email): string
    {
        if (filled($email->text_body)) {
            return (string) $email->text_body;
        }

        if (filled($email->html_body)) {
            return trim(html_entity_decode(strip_tags((string) $email->html_body), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return '';
    }

    public static function contentType(MailboxEmail $email): string
    {
        foreach ($email->displayHeaders() as $name => $value) {
            if (strcasecmp($name, 'Content-Type') === 0) {
                return $value;
            }
        }

        if (filled($email->html_body) && filled($email->text_body)) {
            return 'multipart/alternative';
        }

        if (filled($email->html_body)) {
            return 'text/html';
        }

        if (filled($email->text_body)) {
            return 'text/plain';
        }

        return 'unknown';
    }

    public static function isPlainOnly(MailboxEmail $email): bool
    {
        $type = strtolower(self::contentType($email));

        return str_contains($type, 'text/plain') && ! str_contains($type, 'html');
    }

    public static function tabHint(string $tab, MailboxEmail $email): string
    {
        return match ($tab) {
            'html' => filled($email->html_body)
                ? 'Rendered HTML'
                : (filled($email->text_body) ? 'Plain text rendered as HTML' : 'No body'),
            'text' => filled($email->text_body)
                ? 'text/plain body'
                : (filled($email->html_body) ? 'HTML stripped to text' : 'No body'),
            'raw' => 'Full MIME source',
            'headers' => 'Parsed message headers',
            default => '',
        };
    }
}
