<?php

namespace Copain\LaravelMailDashboard\Storage;

use Copain\LaravelMailDashboard\Data\StoredEmail;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Persists captured emails as one JSON file each, on any Laravel
 * filesystem disk (local by default, s3 or anything else via config).
 */
final class MailStore
{
    public function __construct(
        private readonly Filesystem $disk,
        private readonly string $path = '',
    ) {}

    public static function make(): self
    {
        /** @var ?string $disk */
        $disk = config('mail-dashboard.storage.disk');

        if ($disk !== null && $disk !== '') {
            return new self(
                Storage::disk($disk),
                trim((string) config('mail-dashboard.storage.path', 'mail-dashboard'), '/'),
            );
        }

        return new self(Storage::build([
            'driver' => 'local',
            'root' => storage_path('mail-dashboard'),
            'serve' => false,
            'throw' => false,
        ]));
    }

    public function store(StoredEmail $email): void
    {
        $this->disk->put(
            $this->filePath($email->id),
            (string) json_encode($email->toStorageArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );
    }

    /**
     * All captured emails, newest first.
     *
     * @return list<StoredEmail>
     */
    public function all(): array
    {
        $files = $this->emailFiles();

        // Ids start with a sortable timestamp, so sorting file names in
        // reverse order yields newest first.
        rsort($files);

        return array_values(array_filter(array_map(
            fn (string $file) => $this->read($file),
            $files,
        )));
    }

    public function find(string $id): ?StoredEmail
    {
        if (! $this->isValidId($id)) {
            return null;
        }

        return $this->read($this->filePath($id));
    }

    public function forget(string $id): bool
    {
        if (! $this->isValidId($id) || ! $this->disk->exists($this->filePath($id))) {
            return false;
        }

        return $this->disk->delete($this->filePath($id));
    }

    public function clear(): int
    {
        $files = $this->emailFiles();

        $this->disk->delete($files);

        return count($files);
    }

    public function description(): string
    {
        /** @var ?string $disk */
        $disk = config('mail-dashboard.storage.disk');

        return $disk !== null && $disk !== ''
            ? "disk:{$disk}"
            : 'storage/mail-dashboard';
    }

    private function read(string $file): ?StoredEmail
    {
        $contents = $this->disk->get($file);

        if ($contents === null) {
            return null;
        }

        $data = json_decode($contents, true);

        return is_array($data) ? StoredEmail::fromArray($data) : null;
    }

    /**
     * @return list<string>
     */
    private function emailFiles(): array
    {
        return array_values(array_filter(
            $this->disk->files($this->path),
            fn (string $file) => str_ends_with($file, '.json'),
        ));
    }

    private function filePath(string $id): string
    {
        return ($this->path === '' ? '' : $this->path.'/').$id.'.json';
    }

    private function isValidId(string $id): bool
    {
        return preg_match('/^[0-9]{20}_[a-f0-9]{16}$/', $id) === 1;
    }
}
