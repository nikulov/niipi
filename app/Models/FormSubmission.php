<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];
    
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
