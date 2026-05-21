<?php

declare(strict_types=1);

namespace LaravelMailbox\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use LaravelMailbox\Contracts\EmailRepositoryContract;
use LaravelMailbox\Data\EmailFilterData;
use LaravelMailbox\Models\MailboxEmail;
use LaravelMailbox\Storage\ArrayEmailStorage;

final class EmailQueryService
{
    public function __construct(
        private readonly EmailRepositoryContract $repository,
        private readonly ?ArrayEmailStorage $arrayStorage = null,
    ) {}

    public function find(string $identifier): ?MailboxEmail
    {
        if (is_numeric($identifier)) {
            return $this->repository->findById((int) $identifier)
                ?? $this->repository->findByUuid($identifier);
        }

        return $this->repository->findByUuid($identifier);
    }

    /**
     * @return LengthAwarePaginator<int, MailboxEmail>
     */
    public function paginate(EmailFilterData $filter, int $perPage = 25): LengthAwarePaginator
    {
        if ($this->arrayStorage !== null) {
            return $this->paginateArray($filter, $perPage);
        }

        return $this->repository->paginate($filter, $perPage);
    }

    /**
     * @return LengthAwarePaginator<int, MailboxEmail>
     */
    private function paginateArray(EmailFilterData $filter, int $perPage): LengthAwarePaginator
    {
        $collection = collect($this->arrayStorage?->all() ?? [])
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
            ->values();

        $page = max(1, (int) request()->get('page', 1));
        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }
}
