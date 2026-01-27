<?php

namespace App\Models;

use App\Enums\FormSubmissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'status',
        'data',
        'ip',
        'user_agent',
        'error_message',
    ];
    
    protected $casts = [
        'data' => 'array',
        'status' => FormSubmissionStatus::class,
    ];
    
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
    
    public function files(): HasMany
    {
        return $this->hasMany(FormSubmissionFile::class);
    }
}
