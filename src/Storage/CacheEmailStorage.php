<?php

declare(strict_types=1);

namespace LaravelMailbox\Storage;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use LaravelMailbox\Contracts\EmailStorageContract;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Models\MailboxEmail;

final class CacheEmailStorage implements EmailStorageContract
{
    private const CACHE_KEY = 'mailbox:emails';

    public function __construct(
        private readonly CacheRepository $cache,
    ) {}

    public function store(CapturedEmailData $data): MailboxEmail
    {
        $emails = $this->getEmails();
        $id = count($emails) + 1;

        $email = new MailboxEmail(array_merge($data->toArray(), ['id' => $id]));
        $email->exists = true;

        $emails[$id] = $email->toArray();

        $this->cache->put(self::CACHE_KEY, $emails, now()->addDay());

        return $email;
    }

    public function all(): array
    {
        return array_map(
            static function (array $attributes): MailboxEmail {
                $email = new MailboxEmail($attributes);
                $email->exists = true;

                return $email;
            },
            array_values($this->getEmails()),
        );
    }

    public function flush(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getEmails(): array
    {
        return $this->cache->get(self::CACHE_KEY, []);
    }
}
