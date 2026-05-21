<?php

declare(strict_types=1);

use LaravelMailbox\Models\MailboxEmail;

it('renders the mailbox inbox route', function (): void {
    MailboxEmail::query()->create([
        'uuid' => (string) Illuminate\Support\Str::uuid(),
        'subject' => 'Inbox test',
        'to' => [['address' => 'test@example.com', 'name' => null]],
        'from' => [['address' => 'from@example.com', 'name' => null]],
        'sent_at' => now(),
    ]);

    $this->get('/mailbox')
        ->assertOk()
        ->assertSee('Inbox test');
});

it('deletes an email via route', function (): void {
    $email = MailboxEmail::query()->create([
        'uuid' => (string) Illuminate\Support\Str::uuid(),
        'subject' => 'Delete me',
        'to' => [['address' => 'test@example.com', 'name' => null]],
        'sent_at' => now(),
    ]);

    $this->deleteJson('/mailbox/emails/'.$email->id)
        ->assertNoContent();

    expect(MailboxEmail::query()->find($email->id))->toBeNull();
});
