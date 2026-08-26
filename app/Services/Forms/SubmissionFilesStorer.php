<?php

namespace App\Services\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class SubmissionFilesStorer
{
    /**
     * @param  array<int, array{disk: string, path: string}>  $stored  заполняется по ссылке:
     *                                                                 вызывающий должен уметь удалить уже записанные файлы, даже если store()
     *                                                                 упал на середине
     */
    public function store(Form $form, FormSubmission $submission, array $uploads, array &$stored = []): void
    {
        foreach ($form->fields as $field) {
            if (! $field->is_enabled || $field->type !== 'file') {
                continue;
            }

            $value = $uploads[$field->name] ?? null;

            if (! $value) {
                continue;
            }

            $cfg = is_array($field->extra) ? $field->extra : [];
            $multiple = (bool) ($cfg['multiple'] ?? false);

            // Normalize to array of files
            $files = $multiple ? (is_array($value) ? $value : [$value]) : [$value];

            $disk = (string) Arr::get($cfg, 'disk', 'public');

            // Prefer slug if you want stable path; here keep your current logic
            $dir = (string) Arr::get($cfg, 'dir', "forms/{$form->id}/{$submission->id}");

            // Sanitize path to prevent directory traversal attacks
            $dir = str_replace(['..', '\\'], '', $dir);
            $dir = ltrim($dir, '/');

            foreach ($files as $upload) {
                if (! $upload) {
                    continue;
                }

                $path = $upload->store($dir, $disk);

                // `TemporaryUploadedFile::storeAs()` discards what `put()`
                // returned and always hands back the path it composed, so a
                // failed write would otherwise be recorded as a healthy row
                // pointing at nothing. Bug #42
                if (! $path || ! Storage::disk($disk)->exists($path)) {
                    Log::warning('form submission file was not stored', [
                        'submission' => $submission->id,
                        'field' => $field->name,
                        'disk' => $disk,
                        'path' => $path,
                    ]);

                    throw ValidationException::withMessages([
                        "uploads.{$field->name}" => __('panel.upload_not_saved'),
                    ]);
                }

                $stored[] = ['disk' => $disk, 'path' => $path];

                FormSubmissionFile::create([
                    'form_submission_id' => $submission->id,
                    'field_name' => $field->name,
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => method_exists($upload, 'getClientOriginalName') ? $upload->getClientOriginalName() : null,
                    'mime_type' => method_exists($upload, 'getMimeType') ? $upload->getMimeType() : null,
                    'size' => method_exists($upload, 'getSize') ? $upload->getSize() : null,
                ]);
            }
        }
    }
}
