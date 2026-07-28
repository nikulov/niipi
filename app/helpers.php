<?php

use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

if (! function_exists('public_asset')) {
    function public_asset(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/')
        ) {
            return $path;
        }

        try {
            return Storage::disk('public')->url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (! function_exists('generate_uploaded_file_name')) {
    function generate_uploaded_file_name(TemporaryUploadedFile $file, int $limit = 20): string
    {
        return str(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        )
            ->slug()
            ->limit($limit)
            ->append('-'.time().'.'.$file->getClientOriginalExtension())
            ->toString();
    }
}
