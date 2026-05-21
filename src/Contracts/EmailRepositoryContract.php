<?php

declare(strict_types=1);

namespace LaravelMailbox\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Data\EmailFilterData;
use LaravelMailbox\Models\MailboxEmail;

interface EmailRepositoryContract
{
    public function store(CapturedEmailData $data): MailboxEmail;

    public function findByUuid(string $uuid): ?MailboxEmail;

    public function findById(int $id): ?MailboxEmail;

    /**
     * @return LengthAwarePaginator<int, MailboxEmail>
     */
    public function paginate(EmailFilterData $filter, int $perPage = 25): LengthAwarePaginator;

    /**
     * @return CursorPaginator<int, MailboxEmail>
     */
    public function cursorPaginate(EmailFilterData $filter, int $perPage = 25): CursorPaginator;

    public function delete(MailboxEmail $email): bool;

    public function deleteAll(): int;

    public function count(): int;

    public function pruneOlderThan(\DateTimeInterface $date): int;
}
