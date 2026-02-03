<?php

namespace Tests\Unit\Models;

use App\Enums\FormSubmissionStatus;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormTest extends TestCase
{
    use RefreshDatabase;

    public function test_fields_relation_is_sorted(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'second',
            'label' => 'Second',
            'sort' => 2,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'first',
            'label' => 'First',
            'sort' => 1,
        ]);

        $names = $form->fields->pluck('name')->all();

        $this->assertSame(['first', 'second'], $names);
    }

    public function test_submissions_relation(): void
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

        $this->assertSame([$submission->id], $form->submissions()->pluck('id')->all());
    }

    public function test_casts_settings_and_user_mail_attachments(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'settings' => ['submit_label' => 'Send'],
            'user_mail_attachments' => ['files/a.pdf'],
            'is_active' => true,
        ]);

        $this->assertIsArray($form->settings);
        $this->assertIsArray($form->user_mail_attachments);
        $this->assertIsBool($form->is_active);
    }
}
