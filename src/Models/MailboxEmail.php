<?php

declare(strict_types=1);

namespace LaravelMailbox\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Support\EmailContent;
use LaravelMailbox\Support\EmailHeaderParser;
use LaravelMailbox\Support\EmailPreviewBuilder;

/**
 * @property int $id
 * @property string $uuid
 * @property string|null $message_id
 * @property string|null $subject
 * @property string|null $html_body
 * @property string|null $text_body
 * @property array<int, array{address: string, name: ?string}>|null $from
 * @property array<int, array{address: string, name: ?string}>|null $to
 * @property array<int, array{address: string, name: ?string}>|null $cc
 * @property array<int, array{address: string, name: ?string}>|null $bcc
 * @property array<int, array{address: string, name: ?string}>|null $reply_to
 * @property array<string, string>|null $headers
 * @property array<int, array<string, mixed>>|null $attachments
 * @property string|null $mailer
 * @property string|null $queue
 * @property array<int, string>|null $tags
 * @property string|null $raw_source
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MailboxEmail extends Model
{
    protected $table = 'mailbox_emails';

    protected $fillable = [
        'uuid',
        'message_id',
        'subject',
        'html_body',
        'text_body',
        'from',
        'to',
        'cc',
        'bcc',
        'reply_to',
        'headers',
        'attachments',
        'mailer',
        'queue',
        'tags',
        'raw_source',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'from' => 'array',
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'reply_to' => 'array',
            'headers' => 'array',
            'attachments' => 'array',
            'tags' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function hasTo(string $address): bool
    {
        return $this->containsAddress($this->to ?? [], $address);
    }

    public function hasFrom(string $address): bool
    {
        return $this->containsAddress($this->from ?? [], $address);
    }

    public function hasCc(string $address): bool
    {
        return $this->containsAddress($this->cc ?? [], $address);
    }

    public function hasBcc(string $address): bool
    {
        return $this->containsAddress($this->bcc ?? [], $address);
    }

    public function hasSubject(string $subject): bool
    {
        return str_contains((string) $this->subject, $subject);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? [], true);
    }

    public function previewHtmlDocument(): string
    {
        return EmailPreviewBuilder::document($this->html_body, $this->text_body);
    }

    public function defaultPreviewTab(): string
    {
        return filled($this->html_body) ? 'html' : 'text';
    }

    public function plainTextContent(): string
    {
        return EmailContent::plainText($this);
    }

    public function contentType(): string
    {
        return EmailContent::contentType($this);
    }

    public function tabHint(string $tab): string
    {
        return EmailContent::tabHint($tab, $this);
    }

    /**
     * @return array<string, string>
     */
    public function displayHeaders(): array
    {
        $headers = $this->headers ?? [];

        if (count($headers) < 4 && filled($this->raw_source)) {
            $headers = array_merge(EmailHeaderParser::parse($this->raw_source), $headers);
        }

        return EmailHeaderParser::sortForDisplay($headers);
    }

    public function toCapturedData(): CapturedEmailData
    {
        return CapturedEmailData::fromArray([
            'uuid' => $this->uuid,
            'message_id' => $this->message_id,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'reply_to' => $this->reply_to,
            'headers' => $this->headers,
            'attachments' => $this->attachments,
            'mailer' => $this->mailer,
            'queue' => $this->queue,
            'tags' => $this->tags,
            'raw_source' => $this->raw_source,
            'sent_at' => $this->sent_at?->toDateTimeString(),
        ]);
    }

    /**
     * @param  array<int, array{address: string, name: ?string}>  $addresses
     */
    private function containsAddress(array $addresses, string $address): bool
    {
        foreach ($addresses as $entry) {
            if (strcasecmp($entry['address'], $address) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFilter(Builder $query, \LaravelMailbox\Data\EmailFilterData $filter): Builder
    {
        if ($filter->search) {
            $search = '%'.$filter->search.'%';
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('subject', 'like', $search)
                    ->orWhere('text_body', 'like', $search)
                    ->orWhere('html_body', 'like', $search);
            });
        }

        if ($filter->recipient) {
            $recipient = '%'.$filter->recipient.'%';
            $query->where(function (Builder $builder) use ($recipient): void {
                $builder
                    ->where('to', 'like', $recipient)
                    ->orWhere('cc', 'like', $recipient)
                    ->orWhere('bcc', 'like', $recipient);
            });
        }

        if ($filter->subject) {
            $query->where('subject', 'like', '%'.$filter->subject.'%');
        }

        if ($filter->mailer) {
            $query->where('mailer', $filter->mailer);
        }

        return $query;
    }
}
