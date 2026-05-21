<?php

declare(strict_types=1);

namespace LaravelMailbox\Support;

use Illuminate\Support\Str;
use LaravelMailbox\Data\CapturedEmailData;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
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
            htmlBody: $htmlBody !== false ? $htmlBody : null,
            textBody: $textBody !== false ? $textBody : null,
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
        $email = $message instanceof Email ? $message : Email::fromString($message->toString());

        return $this->parse($email, $mailer, $queue);
    }

    /**
     * @return array<int, array{name: string, content_type: string, size: int, path: ?string}>
     */
    private function extractAttachments(Email $email): array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            if (! $attachment instanceof DataPart) {
                continue;
            }

            $body = $attachment->getBody();

            if (is_resource($body)) {
                $content = stream_get_contents($body) ?: '';
            } else {
                $content = (string) $body;
            }

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
