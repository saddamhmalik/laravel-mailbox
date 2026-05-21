<?php

declare(strict_types=1);

namespace LaravelMailbox\Data;

use DateTimeInterface;

final readonly class CapturedEmailData
{
    /**
     * @param  array<int, array{address: string, name: ?string}>  $from
     * @param  array<int, array{address: string, name: ?string}>  $to
     * @param  array<int, array{address: string, name: ?string}>  $cc
     * @param  array<int, array{address: string, name: ?string}>  $bcc
     * @param  array<int, array{address: string, name: ?string}>  $replyTo
     * @param  array<string, string>  $headers
     * @param  array<int, array{name: string, content_type: string, size: int, path: ?string}>  $attachments
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public string $uuid,
        public ?string $messageId,
        public ?string $subject,
        public ?string $htmlBody,
        public ?string $textBody,
        public array $from,
        public array $to,
        public array $cc,
        public array $bcc,
        public array $replyTo,
        public array $headers,
        public array $attachments,
        public ?string $mailer,
        public ?string $queue,
        public array $tags,
        public ?string $rawSource,
        public ?DateTimeInterface $sentAt,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            uuid: (string) $attributes['uuid'],
            messageId: $attributes['message_id'] ?? null,
            subject: $attributes['subject'] ?? null,
            htmlBody: $attributes['html_body'] ?? null,
            textBody: $attributes['text_body'] ?? null,
            from: (array) ($attributes['from'] ?? []),
            to: (array) ($attributes['to'] ?? []),
            cc: (array) ($attributes['cc'] ?? []),
            bcc: (array) ($attributes['bcc'] ?? []),
            replyTo: (array) ($attributes['reply_to'] ?? []),
            headers: (array) ($attributes['headers'] ?? []),
            attachments: (array) ($attributes['attachments'] ?? []),
            mailer: $attributes['mailer'] ?? null,
            queue: $attributes['queue'] ?? null,
            tags: (array) ($attributes['tags'] ?? []),
            rawSource: $attributes['raw_source'] ?? null,
            sentAt: isset($attributes['sent_at']) ? new \DateTimeImmutable((string) $attributes['sent_at']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'message_id' => $this->messageId,
            'subject' => $this->subject,
            'html_body' => $this->htmlBody,
            'text_body' => $this->textBody,
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'reply_to' => $this->replyTo,
            'headers' => $this->headers,
            'attachments' => $this->attachments,
            'mailer' => $this->mailer,
            'queue' => $this->queue,
            'tags' => $this->tags,
            'raw_source' => $this->rawSource,
            'sent_at' => $this->sentAt?->format('Y-m-d H:i:s'),
        ];
    }
}
