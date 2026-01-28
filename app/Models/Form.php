<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Form extends Model
{
    protected $fillable = [
        'title',
        'name',
        'slug',
        'recipient_admin_email',
        'is_active',
        'is_modal',
        'settings',
    ];
    
    protected $casts = [
        'is_active' => 'bool',
        'is_modal' => 'bool',
        'settings' => 'array',
    ];
    
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort');
    }
    
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }
    
    public function files(): HasMany
    {
        return $this->hasMany(FormSubmissionFile::class, 'form_submission_id');
    }
}
