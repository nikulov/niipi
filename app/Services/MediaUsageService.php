<?php

namespace App\Services;

use App\Enums\MediaFileType;
use App\Models\MediaFile;
use App\Models\MediaFileUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaUsageService
{
    private const FILE_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp', 'avif',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv',
        'mp4', 'mp3', 'wav', 'ogg', 'webm',
        'zip', 'rar', '7z',
    ];

    private const SKIP_ATTRIBUTES = [
        'id', 'created_at', 'updated_at', 'published_at', 'deleted_at',
        'slug', 'password', 'remember_token', 'email_verified_at',
    ];

    public function syncForModel(Model $model): void
    {
        $currentPaths = $this->extractPaths($model);

        $existingUsages = MediaFileUsage::query()
            ->where('usable_type', $model->getMorphClass())
            ->where('usable_id', $model->getKey())
            ->get();

        $existingByKey = $existingUsages->keyBy(
            fn (MediaFileUsage $u) => $u->media_file_id.':'.$u->field
        );

        $desiredUsages = [];

        foreach ($currentPaths as $field => $paths) {
            foreach ($paths as $path) {
                $mediaFile = $this->findOrCreateMediaFile($path);
                if (! $mediaFile) {
                    continue;
                }

                $key = $mediaFile->id.':'.$field;
                $desiredUsages[$key] = [
                    'media_file_id' => $mediaFile->id,
                    'field' => $field,
                ];
            }
        }

        foreach ($existingByKey as $key => $usage) {
            if (! isset($desiredUsages[$key])) {
                $usage->delete();
            }
        }

        foreach ($desiredUsages as $key => $data) {
            if (! $existingByKey->has($key)) {
                MediaFileUsage::firstOrCreate([
                    'media_file_id' => $data['media_file_id'],
                    'usable_type' => $model->getMorphClass(),
                    'usable_id' => $model->getKey(),
                    'field' => $data['field'],
                ]);
            }
        }
    }

    public function removeAllForModel(Model $model): void
    {
        MediaFileUsage::query()
            ->where('usable_type', $model->getMorphClass())
            ->where('usable_id', $model->getKey())
            ->delete();
    }

    public function extractPaths(Model $model): array
    {
        $result = [];

        foreach ($model->getAttributes() as $key => $value) {
            if (in_array($key, self::SKIP_ATTRIBUTES, true)) {
                continue;
            }
            if ($value === null || is_bool($value) || is_numeric($value)) {
                continue;
            }
            if (! is_string($value)) {
                continue;
            }

            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                $paths = $this->extractFilePathsRecursive($decoded);
                if ($paths) {
                    $result[$key] = $paths;
                }
            } elseif ($this->looksLikeFilePath($value)) {
                $result[$key] = [$value];
            }
        }

        return $result;
    }

    public function registerFile(string $path, string $disk = 'public'): ?MediaFile
    {
        return $this->findOrCreateMediaFile($path, $disk);
    }

    private function extractFilePathsRecursive(mixed $data): array
    {
        $paths = [];

        if (is_string($data)) {
            if ($this->looksLikeFilePath($data)) {
                $paths[] = $data;
            }

            return $paths;
        }

        if (is_array($data)) {
            foreach ($data as $value) {
                $paths = array_merge($paths, $this->extractFilePathsRecursive($value));
            }
        }

        return $paths;
    }

    private function looksLikeFilePath(string $value): bool
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return false;
        }

        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        if (! in_array($extension, self::FILE_EXTENSIONS, true)) {
            return false;
        }

        if (! str_contains($value, '/')) {
            return false;
        }

        return true;
    }

    private function findOrCreateMediaFile(string $path, string $disk = 'public'): ?MediaFile
    {
        $existing = MediaFile::where('path', $path)->where('disk', $disk)->first();
        if ($existing) {
            return $existing;
        }

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $mimeType = null;
        $size = 0;

        try {
            $mimeType = Storage::disk($disk)->mimeType($path);
            $size = Storage::disk($disk)->size($path);
        } catch (\Throwable) {
        }

        return MediaFile::firstOrCreate(
            ['path' => $path, 'disk' => $disk],
            [
                'filename' => basename($path),
                'mime_type' => $mimeType,
                'size' => $size,
                'type' => MediaFileType::fromMimeType($mimeType)->value,
                'uploaded_by' => auth()->id(),
            ]
        );
    }
}
