<?php

namespace App\Models;

use App\Enums\FormApplicantType;
use App\Models\Concerns\Duplicatable;
use App\Models\Concerns\TracksMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Form extends Model
{
    use Duplicatable;

    // Mail attachments live in `user_mail_attachments`. Without usage tracking
    // the media manager shows those files as unused and offers to delete them.
    use TracksMediaUsage;

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
        'applicant_type',
        'settings',
        'success_message',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'settings' => 'array',
        'send_admin_mail' => 'bool',
        'send_user_mail' => 'bool',
        'user_mail_attachments' => 'array',
        'applicant_type' => FormApplicantType::class,
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }

    public function duplicateTitleColumn(): string
    {
        return 'name';
    }

    public function duplicateSlugColumn(): ?string
    {
        return null;
    }

    public function prepareDuplicate(Model $copy): void
    {
        $copy->is_active = false;
    }

    public function copyRelationsTo(Model $copy): void
    {
        $this->loadMissing('fields');

        foreach ($this->fields as $field) {
            $new = $field->replicate();
            $new->form_id = $copy->id;
            $new->save();
        }
    }
}
