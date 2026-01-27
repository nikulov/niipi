<?php

namespace App\Services\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use Illuminate\Support\Arr;

final class SubmissionFilesStorer
{
    public function store(Form $form, FormSubmission $submission, array $uploads): void
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
            
            foreach ($files as $upload) {
                if (! $upload) {
                    continue;
                }
                
                $path = $upload->store($dir, $disk);
                
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