<?php

namespace App\Filament\Resources\MediaFiles\Pages;

use App\Enums\MediaFileType;
use App\Filament\Resources\MediaFiles\MediaFileResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMediaFile extends CreateRecord
{
    protected static string $resource = MediaFileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['path'] ?? null;

        if ($path && Storage::disk('public')->exists($path)) {
            $data['filename'] = basename($path);
            $data['mime_type'] = Storage::disk('public')->mimeType($path);
            $data['size'] = Storage::disk('public')->size($path);
            $data['type'] = MediaFileType::fromMimeType($data['mime_type'])->value;
        }

        $data['disk'] = 'public';
        $data['uploaded_by'] = auth()->id();

        return $data;
    }
}
