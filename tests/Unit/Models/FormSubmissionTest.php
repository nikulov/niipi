<?php

namespace Tests\Unit\Models;

use App\Enums\FormSubmissionStatus;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_relation_and_status_cast(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmissionStatus::Processing,
            'data' => ['name' => 'Jane'],
        ]);

        $this->assertSame($form->id, $submission->form->id);
        $this->assertInstanceOf(FormSubmissionStatus::class, $submission->status);
        $this->assertSame(FormSubmissionStatus::Processing, $submission->status);
        $this->assertIsArray($submission->data);
    }
}
