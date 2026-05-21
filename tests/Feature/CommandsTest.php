<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use LaravelMailbox\Models\MailboxEmail;

it('clears captured emails', function (): void {
    MailboxEmail::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'subject' => 'One',
        'sent_at' => now(),
    ]);

    $this->artisan('mailbox:clear')
        ->assertSuccessful();

    expect(MailboxEmail::query()->count())->toBe(0);
});

it('prunes old emails', function (): void {
    $old = MailboxEmail::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'subject' => 'Old',
        'sent_at' => now()->subDays(30),
    ]);

    $old->forceFill([
        'created_at' => now()->subDays(30),
        'updated_at' => now()->subDays(30),
    ])->save();

    MailboxEmail::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'subject' => 'New',
        'sent_at' => now(),
    ]);

    $this->artisan('mailbox:prune', ['--days' => 7])
        ->assertSuccessful();

    expect(MailboxEmail::query()->count())->toBe(1);
});

it('runs install command', function (): void {
    $this->artisan('mailbox:install', ['--no-assets' => true])
        ->expectsQuestion('Run mailbox migrations now?', false)
        ->assertSuccessful();
});
