<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;

it('registers mailbox prune on the schedule', function (): void {
    $schedule = app(Schedule::class);
    $events = collect($schedule->events());

    $prune = $events->first(fn ($event): bool => str_contains($event->command ?? '', 'mailbox:prune'));

    expect($prune)->not->toBeNull();
});
