<?php

declare(strict_types=1);

namespace LaravelMailbox\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use LaravelMailbox\Services\EmailQueryService;

final class EmailController
{
    public function __construct(
        private readonly EmailQueryService $queryService,
    ) {}

    public function destroy(string $id): RedirectResponse|Response
    {
        $email = $this->queryService->find($id);

        if ($email === null) {
            abort(404);
        }

        $email->delete();

        if (request()->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->route('mailbox.index');
    }
}
