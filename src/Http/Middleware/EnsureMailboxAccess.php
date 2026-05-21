<?php

declare(strict_types=1);

namespace LaravelMailbox\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureMailboxAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mailbox.enabled', true)) {
            abort(404);
        }

        if (config('mailbox.local_only', true) && ! app()->environment('local', 'testing')) {
            abort(403, 'Mailbox is only available in local environments.');
        }

        $callback = config('mailbox.authorization');

        if (is_callable($callback) && ! $callback($request)) {
            abort(403, 'You are not authorized to access the mailbox.');
        }

        return $next($request);
    }
}
