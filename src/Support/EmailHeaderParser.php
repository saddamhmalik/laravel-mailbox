<?php

declare(strict_types=1);

namespace LaravelMailbox\Support;

final class EmailHeaderParser
{
    /**
     * @return array<string, string>
     */
    public static function parse(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (! preg_match('/\A(.*?)(?:\r?\n\r?\n)/s', $raw, $matches)) {
            return [];
        }

        $headers = [];
        $current = null;

        foreach (preg_split('/\r?\n/', $matches[1]) as $line) {
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\s+/', $line) && $current !== null) {
                $headers[$current] .= ' '.trim($line);

                continue;
            }

            if (! str_contains($line, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $current = trim($name);
            $headers[$current] = trim($value);
        }

        return $headers;
    }

    /**
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    public static function sortForDisplay(array $headers): array
    {
        $priority = [
            'From', 'To', 'Cc', 'Bcc', 'Reply-To', 'Subject',
            'Date', 'Message-ID', 'MIME-Version', 'Content-Type', 'Content-Transfer-Encoding',
        ];

        $sorted = [];

        foreach ($priority as $name) {
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, $name) === 0) {
                    $sorted[$key] = $value;
                    unset($headers[$key]);
                }
            }
        }

        ksort($headers, SORT_NATURAL | SORT_FLAG_CASE);

        return array_merge($sorted, $headers);
    }
}
