<?php

declare(strict_types=1);

namespace LaravelMailbox\Services;

use Closure;
use LaravelMailbox\Contracts\EmailRepositoryContract;
use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Storage\ArrayEmailStorage;
use LaravelMailbox\Testing\MailboxAssertions;

final class MailboxService implements MailboxContract
{
    public function __construct(
        private readonly EmailCaptureService $captureService,
        private readonly EmailRepositoryContract $repository,
        private readonly ?ArrayEmailStorage $arrayStorage = null,
    ) {}

    public function capture(CapturedEmailData $data): void
    {
        $this->captureService->capture($data);
    }

    public function assertSent(Closure|int|null $callback = null): MailboxAssertions
    {
        $assertions = new MailboxAssertions($this->emails());

        if (is_int($callback)) {
            return $assertions->assertSentCount($callback);
        }

        if ($callback === null) {
            return $assertions->assertSent();
        }

        return $assertions->assertSent($callback);
    }

    public function assertNothingSent(): MailboxAssertions
    {
        return (new MailboxAssertions($this->emails()))->assertNothingSent();
    }

    public function assertSentCount(int $count): MailboxAssertions
    {
        return (new MailboxAssertions($this->emails()))->assertSentCount($count);
    }

    public function flush(): void
    {
        if ($this->arrayStorage !== null) {
            $this->arrayStorage->flush();

            return;
        }

        $this->repository->deleteAll();
    }

    public function count(): int
    {
        if ($this->arrayStorage !== null) {
            return count($this->arrayStorage->all());
        }

        return $this->repository->count();
    }

    /**
     * @return array<int, \LaravelMailbox\Models\MailboxEmail>
     */
    public function emails(): array
    {
        if ($this->arrayStorage !== null) {
            return $this->arrayStorage->all();
        }

        return \LaravelMailbox\Models\MailboxEmail::query()
            ->orderByDesc('created_at')
            ->get()
            ->all();
    }
}
