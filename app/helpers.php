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
    function generate_uploaded_file_name(TemporaryUploadedFile $file, int $limit = 50): string
    {
        return str(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        )
            ->slug()
            ->limit($limit, '')
            ->append('-'.time().'.'.$file->getClientOriginalExtension())
            ->toString();
    }
}

if (! function_exists('inline_svg')) {
    /**
     * Read an SVG file for inlining in Blade.
     *
     * Resolves the path against `resource_path()` first (app static assets like
     * `images/layout/...svg`), then falls back to the `public` storage disk
     * (admin-uploaded media like `images/footer/...svg`). Returns '' when the
     * path leaves those two roots, is not a `.svg`, or the file is missing.
     *
     * The path arrives as a plain string inside a JSON column, so `..` has to be
     * rejected here: neither `resource_path()` nor `Storage::path()` normalises
     * it, and `../.env` would otherwise be inlined into the page.
     *
     * Never reads via HTTP — `public_asset()` returns an absolute URL, and
     * fetching it made the server request itself through DNS (bug #26).
     */
    function inline_svg(?string $path): string
    {
        if (! $path || str_starts_with($path, 'http')) {
            return '';
        }

        $clean = ltrim($path, '/');

        if (str_contains($clean, '..') || ! str_ends_with(strtolower($clean), '.svg')) {
            return '';
        }

        foreach ([resource_path($clean), Storage::disk('public')->path($clean)] as $candidate) {
            if (is_file($candidate)) {
                return (string) file_get_contents($candidate);
            }
        }

        return '';
    }
}
