<?php

namespace App\Services\Forms;

use App\Models\Form;
use App\Models\FormSubmission;

final class SubmissionCreator
{
    public function create(Form $form, array $data, ?string $ip, ?string $userAgent): FormSubmission
    {
        return FormSubmission::create([
            'form_id' => $form->id,
            'status' => 'new',
            'data' => $data,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}