<?php

namespace Copain\LaravelMailDashboard\Data;

final readonly class Attachment
{
    public function __construct(
        public string $filename,
        public string $contentType,
        public int $size,
        public ?string $contentId,
        public string $content,
    ) {}

    /**
     * @param  array{filename: string, content_type: string, size: int, content_id: ?string, content: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            filename: $data['filename'],
            contentType: $data['content_type'],
            size: $data['size'],
            contentId: $data['content_id'],
            content: (string) base64_decode($data['content'], true),
        );
    }

    /**
     * @return array{filename: string, content_type: string, size: int, content_id: ?string, content: string}
     */
    public function toStorageArray(): array
    {
        return [
            'filename' => $this->filename,
            'content_type' => $this->contentType,
            'size' => $this->size,
            'content_id' => $this->contentId,
            'content' => base64_encode($this->content),
        ];
    }

    /**
     * @return array{filename: string, content_type: string, size: int, content_id: ?string}
     */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'content_type' => $this->contentType,
            'size' => $this->size,
            'content_id' => $this->contentId,
        ];
    }
}
