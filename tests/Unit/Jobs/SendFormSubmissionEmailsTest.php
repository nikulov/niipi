<?php

namespace Tests\Unit\Jobs;

use App\Enums\FormSubmissionStatus;
use App\Jobs\SendFormSubmissionEmails;
use App\Mail\AdminFormSubmissionMail;
use App\Mail\TemplatedFormSubmissionMail;
use App\Mail\UserFormSubmissionMail;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\Forms\FormEmailTemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SendFormSubmissionEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_default_admin_and_user_emails_and_updates_status(): void
    {
        Mail::fake();

        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'recipient_admin_email' => 'admin@example.com',
            'send_admin_mail' => true,
            'send_user_mail' => true,
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmissionStatus::New,
            'data' => ['email' => 'user@example.com'],
        ]);

        $job = new SendFormSubmissionEmails($submission->id);
        $job->handle(app(FormEmailTemplateRenderer::class));

        Mail::assertSent(AdminFormSubmissionMail::class, 1);
        Mail::assertSent(UserFormSubmissionMail::class, 1);

        $submission->refresh();
        $this->assertSame(FormSubmissionStatus::Sent, $submission->status);
        $this->assertNull($submission->error_message);
    }

    public function test_sends_templated_emails_with_attachments(): void
    {
        Mail::fake();
        Storage::fake('public');

        Storage::disk('public')->put('attachments/test.pdf', 'data');

        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'recipient_admin_email' => 'admin@example.com',
            'send_admin_mail' => true,
            'admin_mail_subject' => 'Admin {{ submission.id }}',
            'admin_mail_body_md' => 'Admin body',
            'send_user_mail' => true,
            'user_mail_subject' => 'User {{ submission.id }}',
            'user_mail_body_md' => 'User body',
            'user_mail_attachments' => ['attachments/test.pdf'],
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmissionStatus::New,
            'data' => ['email' => 'user@example.com'],
        ]);

        $job = new SendFormSubmissionEmails($submission->id);
        $job->handle(app(FormEmailTemplateRenderer::class));

        Mail::assertSent(TemplatedFormSubmissionMail::class, 2);

        Mail::assertSent(TemplatedFormSubmissionMail::class, function (TemplatedFormSubmissionMail $mail) use ($submission) {
            $subject = $mail->envelope()->subject;

            if ($subject === 'User ' . $submission->id) {
                return count($mail->attachments()) === 1;
            }

            return $subject === 'Admin ' . $submission->id;
        });

        $submission->refresh();
        $this->assertSame(FormSubmissionStatus::Sent, $submission->status);
    }
}
