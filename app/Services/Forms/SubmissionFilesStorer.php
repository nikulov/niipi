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
            if (! $field->is_enabled) {
                continue;
            }
            
            if ($field->type !== 'file') {
                continue;
            }
            
            $upload = $uploads[$field->name] ?? null;
            
            if (! $upload) {
                continue;
            }
            
            $disk = (string) Arr::get($field->extra, 'disk', 'public');
            $dir = (string) Arr::get($field->extra, 'dir', "forms/{$form->id}/{$submission->id}");
            
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