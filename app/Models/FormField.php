<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'type',
        'name',
        'label',
        'required',
        'is_enabled',
        'sort',
        'rules',
        'options',
        'extra',
    ];
    
    protected $casts = [
        'required' => 'bool',
        'is_enabled' => 'bool',
        'rules' => 'array',
        'options' => 'array',
        'extra' => 'array',
    ];
    
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}