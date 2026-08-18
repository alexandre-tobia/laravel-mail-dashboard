<?php

namespace Copain\LaravelMailDashboard\Data;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final readonly class StoredEmail
{
    /**
     * @param  list<string>  $to
     * @param  list<string>  $cc
     * @param  list<string>  $bcc
     * @param  list<string>  $replyTo
     * @param  list<Attachment>  $attachments
     * @param  list<array{name: string, value: string}>  $headers
     */
    public function __construct(
        public string $id,
        public ?string $messageId,
        public string $subject,
        public ?string $from,
        public array $to,
        public array $cc,
        public array $bcc,
        public array $replyTo,
        public ?string $date,
        public ?string $sourceClass,
        public ?string $textBody,
        public ?string $htmlBody,
        public array $attachments,
        public array $headers,
        public string $raw,
    ) {}

    public static function fromSymfonyEmail(Email $email, ?string $messageId = null, ?string $sourceClass = null): self
    {
        $attachments = [];

        foreach ($email->getAttachments() as $part) {
            $attachments[] = new Attachment(
                filename: $part->getFilename() ?? 'attachment',
                contentType: $part->getMediaType().'/'.$part->getMediaSubtype(),
                size: strlen($part->getBody()),
                contentId: $part->hasContentId() ? $part->getContentId() : null,
                content: $part->getBody(),
            );
        }

        $headers = [];

        foreach ($email->getHeaders()->all() as $header) {
            $headers[] = ['name' => $header->getName(), 'value' => $header->getBodyAsString()];
        }

        $htmlBody = self::normalizeBody($email->getHtmlBody());

        if ($htmlBody !== null) {
            $htmlBody = self::inlineCidImages($htmlBody, $attachments);
        }

        $date = $email->getDate() ?? new DateTimeImmutable;

        return new self(
            id: $date->format('YmdHisu').'_'.bin2hex(random_bytes(8)),
            messageId: $messageId,
            subject: $email->getSubject() ?? '(no subject)',
            from: self::formatAddresses($email->getFrom())[0] ?? null,
            to: self::formatAddresses($email->getTo()),
            cc: self::formatAddresses($email->getCc()),
            bcc: self::formatAddresses($email->getBcc()),
            replyTo: self::formatAddresses($email->getReplyTo()),
            date: $date->format(DateTimeInterface::ATOM),
            sourceClass: $sourceClass,
            textBody: self::normalizeBody($email->getTextBody()),
            htmlBody: $htmlBody,
            attachments: $attachments,
            headers: $headers,
            raw: $email->toString(),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            messageId: $data['message_id'],
            subject: $data['subject'],
            from: $data['from'],
            to: $data['to'],
            cc: $data['cc'],
            bcc: $data['bcc'],
            replyTo: $data['reply_to'],
            date: $data['date'],
            sourceClass: $data['source_class'] ?? null,
            textBody: $data['text_body'],
            htmlBody: $data['html_body'],
            attachments: array_map(Attachment::fromArray(...), $data['attachments']),
            headers: $data['headers'],
            raw: $data['raw'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorageArray(): array
    {
        return [
            'id' => $this->id,
            'message_id' => $this->messageId,
            'subject' => $this->subject,
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'reply_to' => $this->replyTo,
            'date' => $this->date,
            'source_class' => $this->sourceClass,
            'text_body' => $this->textBody,
            'html_body' => $this->htmlBody,
            'attachments' => array_map(fn (Attachment $attachment) => $attachment->toStorageArray(), $this->attachments),
            'headers' => $this->headers,
            'raw' => $this->raw,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'from' => $this->from,
            'to' => $this->to,
            'date' => $this->date,
            'source_class' => $this->sourceClass,
            'preview' => $this->preview(),
            'has_html' => $this->htmlBody !== null,
            'has_text' => $this->textBody !== null,
            'attachments_count' => count($this->attachments),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDetail(): array
    {
        return [
            'id' => $this->id,
            'message_id' => $this->messageId,
            'subject' => $this->subject,
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'reply_to' => $this->replyTo,
            'date' => $this->date,
            'source_class' => $this->sourceClass,
            'text_body' => $this->textBody,
            'html_body' => $this->htmlBody,
            'attachments' => array_map(fn (Attachment $attachment) => $attachment->toArray(), $this->attachments),
            'headers' => $this->headers,
            'raw' => $this->raw,
            'size' => strlen($this->raw),
        ];
    }

    private function preview(): string
    {
        $source = $this->textBody ?? strip_tags($this->htmlBody ?? '');

        return Str::limit(trim((string) preg_replace('/\s+/', ' ', $source)), 120);
    }

    /**
     * @param  string|resource|null  $body
     */
    private static function normalizeBody(mixed $body): ?string
    {
        if (is_resource($body)) {
            rewind($body);

            return (string) stream_get_contents($body);
        }

        return $body === null ? null : (string) $body;
    }

    /**
     * Replaces "cid:" references in an HTML body with inline data URIs so
     * embedded images render in the preview.
     *
     * @param  list<Attachment>  $attachments
     */
    private static function inlineCidImages(string $html, array $attachments): string
    {
        foreach ($attachments as $attachment) {
            if ($attachment->contentId === null) {
                continue;
            }

            $dataUri = 'data:'.$attachment->contentType.';base64,'.base64_encode($attachment->content);
            $html = str_replace('cid:'.$attachment->contentId, $dataUri, $html);
        }

        return $html;
    }

    /**
     * @param  Address[]  $addresses
     * @return list<string>
     */
    private static function formatAddresses(array $addresses): array
    {
        return array_values(array_map(
            fn (Address $address) => $address->getName() !== ''
                ? $address->getName().' <'.$address->getAddress().'>'
                : $address->getAddress(),
            $addresses,
        ));
    }
}
