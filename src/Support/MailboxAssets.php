<?php

declare(strict_types=1);

namespace LaravelMailbox\Support;

use Illuminate\Support\Facades\File;

final class MailboxAssets
{
    public static function cssPath(): string
    {
        $published = public_path('vendor/mailbox/mailbox.css');

        if (File::exists($published)) {
            return $published;
        }

        return dirname(__DIR__, 2).'/dist/mailbox.css';
    }

    public static function jsPath(): string
    {
        $published = public_path('vendor/mailbox/mailbox.js');

        if (File::exists($published)) {
            return $published;
        }

        return dirname(__DIR__, 2).'/dist/mailbox.js';
    }

    public static function usesVite(): bool
    {
        return (bool) config('mailbox.ui.use_vite', false)
            && File::exists(base_path('vite.config.js'))
            && File::exists(resource_path('css/app.css'));
    }

    public static function render(): string
    {
        if (static::usesVite()) {
            return '';
        }

        $css = static::cssUrl();
        $js = static::jsUrl();

        return <<<HTML
<link rel="stylesheet" href="{$css}">
<script src="{$js}" defer></script>
HTML;
    }

    public static function cssUrl(): string
    {
        if (File::exists(public_path('vendor/mailbox/mailbox.css'))) {
            return asset('vendor/mailbox/mailbox.css');
        }

        $prefix = trim((string) config('mailbox.route.prefix', 'mailbox'), '/');

        return url($prefix.'/assets/mailbox.css');
    }

    public static function jsUrl(): string
    {
        if (File::exists(public_path('vendor/mailbox/mailbox.js'))) {
            return asset('vendor/mailbox/mailbox.js');
        }

        $prefix = trim((string) config('mailbox.route.prefix', 'mailbox'), '/');

        return url($prefix.'/assets/mailbox.js');
    }
}
