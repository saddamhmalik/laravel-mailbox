<?php

declare(strict_types=1);

namespace LaravelMailbox\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LaravelMailbox\Contracts\EmailRepositoryContract;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Data\EmailFilterData;
use LaravelMailbox\Models\MailboxEmail;

final class DatabaseEmailRepository implements EmailRepositoryContract
{
    public function store(CapturedEmailData $data): MailboxEmail
    {
        return MailboxEmail::query()->create($data->toArray());
    }

    public function findByUuid(string $uuid): ?MailboxEmail
    {
        return MailboxEmail::query()->where('uuid', $uuid)->first();
    }

    public function findById(int $id): ?MailboxEmail
    {
        return MailboxEmail::query()->find($id);
    }

    public function paginate(EmailFilterData $filter, int $perPage = 25): LengthAwarePaginator
    {
        return MailboxEmail::query()
            ->filter($filter)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function cursorPaginate(EmailFilterData $filter, int $perPage = 25): CursorPaginator
    {
        return MailboxEmail::query()
            ->filter($filter)
            ->orderByDesc('id')
            ->cursorPaginate($perPage);
    }

    public function delete(MailboxEmail $email): bool
    {
        return (bool) $email->delete();
    }

    public function deleteAll(): int
    {
        return MailboxEmail::query()->delete();
    }

    public function count(): int
    {
        return MailboxEmail::query()->count();
    }

    public function pruneOlderThan(\DateTimeInterface $date): int
    {
        return MailboxEmail::query()
            ->where('created_at', '<', $date)
            ->delete();
    }
}
