<?php

namespace Tests\Unit\Mail;

use App\Mail\AdminFormSubmissionMail;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFormSubmissionMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_envelope_and_content(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => 'new',
            'data' => [],
        ]);

        $mail = new AdminFormSubmissionMail($submission);

        $this->assertSame(__('panel.submission') . ': ' . $form->name, $mail->envelope()->subject);
        $this->assertSame('emails.form-submission-admin', $mail->content()->view);
    }
}
