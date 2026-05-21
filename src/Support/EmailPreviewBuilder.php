<?php

declare(strict_types=1);

namespace LaravelMailbox\Support;

final class EmailPreviewBuilder
{
    public static function document(?string $html, ?string $text): string
    {
        if ($html !== null && trim($html) !== '') {
            return self::ensureDocument($html);
        }

        if ($text !== null && trim($text) !== '') {
            return self::plainToDocument($text);
        }

        return self::emptyDocument();
    }

    private static function ensureDocument(string $html): string
    {
        if (stri_contains($html, '<html')) {
            return $html;
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>';
    }

    private static function plainToDocument(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: system-ui, -apple-system, sans-serif; padding: 2rem; line-height: 1.65; color: #334155; background: #fff; white-space: pre-wrap; word-break: break-word; margin: 0; }
</style>
</head>
<body>{$escaped}</body>
</html>
HTML;
    }

    private static function emptyDocument(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 240px; margin: 0; color: #94a3b8; background: #fafafa; }
</style>
</head>
<body>No HTML content in this message.</body>
</html>
HTML;
    }
}
