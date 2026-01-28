<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
