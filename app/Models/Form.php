<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Form extends Model
{
    protected $fillable = [
        'title',
        'name',
        'recipient_admin_email',
        'send_admin_mail',
        'admin_mail_subject',
        'admin_mail_body_md',
        'send_user_mail',
        'user_mail_subject',
        'user_mail_body_md',
        'user_mail_attachments',
        'is_active',
        'settings',
        'success_message'
    ];
    
    protected $casts = [
        'is_active' => 'bool',
        'settings' => 'array',
        'send_admin_mail' => 'bool',
        'send_user_mail' => 'bool',
        'user_mail_attachments' => 'array',
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
