<?php

namespace App\Filament\Forms\Components;

use App\Enums\MediaFileType;
use App\Models\MediaFile;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MediaGrid extends Field
{
    protected string $view = 'forms.components.media-grid';

    protected bool $imagesOnly = false;

    protected bool $multiple = false;

    protected ?array $acceptedMimeTypes = null;

    protected ?int $maxSize = null;

    protected int $perPage = 36;

    public function imagesOnly(bool $v = true): static
    {
        $this->imagesOnly = $v;

        return $this;
    }

    public function multiple(bool $v = true): static
    {
        $this->multiple = $v;

        return $this;
    }

    public function acceptedMimeTypes(?array $v): static
    {
        $this->acceptedMimeTypes = $v;

        return $this;
    }

    public function maxSize(?int $v): static
    {
        $this->maxSize = $v;

        return $this;
    }

    public function perPage(int $v): static
    {
        $this->perPage = $v;

        return $this;
    }

    public function getImagesOnly(): bool
    {
        return $this->imagesOnly;
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function getAcceptedMimeTypes(): ?array
    {
        return $this->acceptedMimeTypes;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getMediaFiles(Get $get): LengthAwarePaginator
    {
        $search = trim((string) ($get('media_search') ?? ''));
        $page = max(1, (int) ($get('media_page') ?? 1));

        $query = MediaFile::query()
            ->where('disk', 'public')
            ->orderByDesc('created_at');

        if ($this->acceptedMimeTypes) {
            $query->whereIn('mime_type', $this->acceptedMimeTypes);
        } elseif ($this->imagesOnly) {
            $query->where('type', MediaFileType::Image->value);
        }

        if ($this->maxSize) {
            $query->where('size', '<=', $this->maxSize * 1024);
        }

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->where(function ($q) use ($like): void {
                $q->where('filename', 'like', $like)
                    ->orWhere('title', 'like', $like)
                    ->orWhere('path', 'like', $like);
            });
        }

        return $query->paginate(perPage: $this->perPage, page: $page);
    }
}
