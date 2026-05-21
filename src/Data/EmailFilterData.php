<?php

declare(strict_types=1);

namespace LaravelMailbox\Data;

final readonly class EmailFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?string $recipient = null,
        public ?string $subject = null,
        public ?string $mailer = null,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            search: isset($input['search']) ? (string) $input['search'] : null,
            recipient: isset($input['recipient']) ? (string) $input['recipient'] : null,
            subject: isset($input['subject']) ? (string) $input['subject'] : null,
            mailer: isset($input['mailer']) ? (string) $input['mailer'] : null,
        );
    }
}
