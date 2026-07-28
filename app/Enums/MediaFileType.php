<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MediaFileType: string implements HasColor, HasIcon, HasLabel
{
    case Image = 'image';
    case Document = 'document';
    case Other = 'other';

    public function getColor(): ?string
    {
        return match ($this) {
            self::Image => 'success',
            self::Document => 'info',
            self::Other => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Image => __('panel.media_type_image'),
            self::Document => __('panel.media_type_document'),
            self::Other => __('panel.media_type_other'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Image => 'heroicon-o-photo',
            self::Document => 'heroicon-o-document',
            self::Other => 'heroicon-o-paper-clip',
        };
    }

    public static function fromMimeType(?string $mimeType): self
    {
        if (! $mimeType) {
            return self::Other;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return self::Image;
        }

        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ])) {
            return self::Document;
        }

        return self::Other;
    }
}
