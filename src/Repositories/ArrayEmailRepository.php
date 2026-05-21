<?php

declare(strict_types=1);

namespace LaravelMailbox\Repositories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator as CursorPaginatorImpl;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use LaravelMailbox\Contracts\EmailRepositoryContract;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Data\EmailFilterData;
use LaravelMailbox\Models\MailboxEmail;
use LaravelMailbox\Storage\ArrayEmailStorage;

final class ArrayEmailRepository implements EmailRepositoryContract
{
    public function __construct(
        private readonly ArrayEmailStorage $storage,
    ) {}

    public function store(CapturedEmailData $data): MailboxEmail
    {
        return $this->storage->store($data);
    }

    public function findByUuid(string $uuid): ?MailboxEmail
    {
        foreach ($this->storage->all() as $email) {
            if ($email->uuid === $uuid) {
                return $email;
            }
        }

        return null;
    }

    public function findById(int $id): ?MailboxEmail
    {
        foreach ($this->storage->all() as $email) {
            if ($email->id === $id) {
                return $email;
            }
        }

        return null;
    }

    /**
     * @return LengthAwarePaginator<int, MailboxEmail>
     */
    public function paginate(EmailFilterData $filter, int $perPage = 25): LengthAwarePaginator
    {
        $filtered = $this->filterEmails($filter);
        $page = max(1, (int) request()->get('page', 1));
        $items = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        return new Paginator(
            $items,
            count($filtered),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    /**
     * @return CursorPaginator<int, MailboxEmail>
     */
    public function cursorPaginate(EmailFilterData $filter, int $perPage = 25): CursorPaginator
    {
        $filtered = $this->filterEmails($filter);

        return new CursorPaginatorImpl(
            array_slice($filtered, 0, $perPage),
            $perPage,
            null,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    public function delete(MailboxEmail $email): bool
    {
        $emails = $this->storage->all();
        $this->storage->flush();

        foreach ($emails as $stored) {
            if ($stored->id !== $email->id) {
                $this->storage->store($stored->toCapturedData());
            }
        }

        return true;
    }

    public function deleteAll(): int
    {
        $count = count($this->storage->all());
        $this->storage->flush();

        return $count;
    }

    public function count(): int
    {
        return count($this->storage->all());
    }

    public function pruneOlderThan(\DateTimeInterface $date): int
    {
        $pruned = 0;
        $remaining = [];

        foreach ($this->storage->all() as $email) {
            if ($email->created_at && $email->created_at < $date) {
                $pruned++;

                continue;
            }

            $remaining[] = $email;
        }

        $this->storage->flush();

        foreach ($remaining as $email) {
            $this->storage->store($email->toCapturedData());
        }

        return $pruned;
    }

    /**
     * @return array<int, MailboxEmail>
     */
    private function filterEmails(EmailFilterData $filter): array
    {
        return collect($this->storage->all())
            ->filter(function (MailboxEmail $email) use ($filter): bool {
                if ($filter->search) {
                    $search = strtolower($filter->search);
                    $haystack = strtolower(implode(' ', [
                        (string) $email->subject,
                        (string) $email->text_body,
                        (string) $email->html_body,
                    ]));

                    if (! str_contains($haystack, $search)) {
                        return false;
                    }
                }

                if ($filter->recipient && ! $email->hasTo($filter->recipient)) {
                    return false;
                }

                if ($filter->subject && ! $email->hasSubject($filter->subject)) {
                    return false;
                }

                if ($filter->mailer && $email->mailer !== $filter->mailer) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('id')
            ->values()
            ->all();
    }
}
