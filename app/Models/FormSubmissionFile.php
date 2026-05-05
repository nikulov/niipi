<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class FormSubmissionFile extends Model
{
    protected $fillable = [
        'form_submission_id',
        'field_name',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    protected function url(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->disk || ! $this->path) {
                return null;
            }

            try {
                return Storage::disk($this->disk)->url($this->path);
            } catch (Throwable) {
                return null;
            }
        });
    }
}
