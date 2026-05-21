<?php

declare(strict_types=1);

namespace LaravelMailbox\Support;

use Illuminate\Support\Str;
use LaravelMailbox\Data\CapturedEmailData;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

final class EmailMessageParser
{
    public function parse(Email $email, ?string $mailer = null, ?string $queue = null): CapturedEmailData
    {
        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();

        if (is_resource($htmlBody)) {
            $htmlBody = stream_get_contents($htmlBody) ?: null;
        }

        if (is_resource($textBody)) {
            $textBody = stream_get_contents($textBody) ?: null;
        }

        return new CapturedEmailData(
            uuid: (string) Str::uuid(),
            messageId: $this->extractMessageId($email),
            subject: $email->getSubject(),
            htmlBody: is_string($htmlBody) ? $htmlBody : null,
            textBody: is_string($textBody) ? $textBody : null,
            from: AddressNormalizer::fromEmailAddresses($email->getFrom()),
            to: AddressNormalizer::fromEmailAddresses($email->getTo()),
            cc: AddressNormalizer::fromEmailAddresses($email->getCc()),
            bcc: AddressNormalizer::fromEmailAddresses($email->getBcc()),
            replyTo: AddressNormalizer::fromEmailAddresses($email->getReplyTo()),
            headers: $this->extractHeaders($email),
            attachments: $this->extractAttachments($email),
            mailer: $mailer,
            queue: $queue,
            tags: [],
            rawSource: $email->toString(),
            sentAt: now(),
        );
    }

    public function parseRaw(RawMessage $message, ?string $mailer = null, ?string $queue = null): CapturedEmailData
    {
        if ($message instanceof Email) {
            return $this->parse($message, $mailer, $queue);
        }

        if ($message instanceof Message) {
            return $this->parse(MessageConverter::toEmail($message), $mailer, $queue);
        }

        return $this->parseFromRawString($message->toString(), $mailer, $queue);
    }

    private function parseFromRawString(string $raw, ?string $mailer, ?string $queue): CapturedEmailData
    {
        $headers = EmailHeaderParser::parse($raw);
        $textBody = $this->extractBodyFromRaw($raw);

        return new CapturedEmailData(
            uuid: (string) Str::uuid(),
            messageId: $headers['Message-ID'] ?? $headers['Message-Id'] ?? null,
            subject: $headers['Subject'] ?? null,
            htmlBody: null,
            textBody: $textBody !== '' ? $textBody : null,
            from: [],
            to: [],
            cc: [],
            bcc: [],
            replyTo: [],
            headers: $headers,
            attachments: [],
            mailer: $mailer,
            queue: $queue,
            tags: [],
            rawSource: $raw,
            sentAt: now(),
        );
    }

    private function extractBodyFromRaw(string $raw): string
    {
        if (! preg_match('/\r?\n\r?\n(.*)\z/s', $raw, $matches)) {
            return '';
        }

        return trim($matches[1]);
    }

    /**
     * @return array<int, array{name: string, content_type: string, size: int, path: ?string}>
     */
    private function extractAttachments(Email $email): array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $content = $attachment->bodyToString();

            $attachments[] = [
                'name' => $attachment->getFilename() ?? $attachment->getName() ?? 'attachment',
                'content_type' => $attachment->getMediaType().'/'.$attachment->getMediaSubtype(),
                'size' => strlen($content),
                'path' => null,
            ];
        }

        return $attachments;
    }

    private function extractMessageId(Email $email): ?string
    {
        $header = $email->getHeaders()->get('Message-ID');

        return $header?->getBodyAsString();
    }

    /**
     * @return array<string, string>
     */
    private function extractHeaders(Email $email): array
    {
        $fromRaw = EmailHeaderParser::parse($email->toString());
        $fromSymfony = AddressNormalizer::headersFromEmail($email);

        return array_merge($fromRaw, $fromSymfony);
    }
}
