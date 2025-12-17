<?php


use Illuminate\Support\Facades\Storage;

if (!function_exists('public_asset')) {
    function public_asset(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        
        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/')
        )
        {
            return $path;
        }
        
        try {
            return Storage::disk('public')->url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }
}