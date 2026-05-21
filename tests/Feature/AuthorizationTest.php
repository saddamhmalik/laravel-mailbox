<?php

declare(strict_types=1);

it('blocks access outside local environments when local_only is enabled', function (): void {
    config(['mailbox.local_only' => true]);
    $this->app['env'] = 'production';

    $this->get('/mailbox')->assertForbidden();
});

it('denies access when authorization callback returns false', function (): void {
    config([
        'mailbox.local_only' => false,
        'mailbox.authorization' => fn (): bool => false,
    ]);

    $this->get('/mailbox')->assertStatus(403);
});
