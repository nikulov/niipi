<?php

namespace Tests\Unit\Models;

use App\Enums\FormSubmissionStatus;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_relation(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmissionStatus::New,
            'data' => ['email' => 'user@example.com'],
        ]);

        $file = FormSubmissionFile::create([
            'form_submission_id' => $submission->id,
            'field_name' => 'file',
            'path' => 'forms/file.pdf',
        ]);

        $this->assertSame($submission->id, $file->submission->id);
    }
}
