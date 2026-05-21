<?php

declare(strict_types=1);

namespace LaravelMailbox\Http\Controllers;

use Illuminate\Http\Response;
use LaravelMailbox\Support\MailboxAssets;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class AssetController
{
    public function css(): SymfonyResponse
    {
        return $this->fileResponse(MailboxAssets::cssPath(), 'text/css');
    }

    public function js(): SymfonyResponse
    {
        return $this->fileResponse(MailboxAssets::jsPath(), 'application/javascript');
    }

    private function fileResponse(string $path, string $contentType): SymfonyResponse
    {
        if (! is_file($path)) {
            abort(404);
        }

        return response(file_get_contents($path), Response::HTTP_OK, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
