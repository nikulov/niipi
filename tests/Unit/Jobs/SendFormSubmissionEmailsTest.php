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
use Illuminate\Support\Facades\Log;
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

            if ($subject === 'User '.$submission->id) {
                return count($mail->attachments()) === 1;
            }

            return $subject === 'Admin '.$submission->id;
        });

        $submission->refresh();
        $this->assertSame(FormSubmissionStatus::Sent, $submission->status);
    }

    public function test_missing_attachment_is_reported_but_the_letter_still_goes_out(): void
    {
        Mail::fake();
        Storage::fake('public');

        Storage::disk('public')->put('attachments/present.pdf', 'data');

        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'send_admin_mail' => false,
            'send_user_mail' => true,
            'user_mail_subject' => 'User {{ submission.id }}',
            'user_mail_body_md' => 'User body',
            // The second file was deleted from the media library after the form
            // had been set up — that is the scenario this covers.
            'user_mail_attachments' => ['attachments/present.pdf', 'attachments/gone.pdf'],
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmissionStatus::New,
            'data' => ['email' => 'user@example.com'],
        ]);

        Log::spy();

        $job = new SendFormSubmissionEmails($submission->id);
        $job->handle(app(FormEmailTemplateRenderer::class));

        Mail::assertSent(
            TemplatedFormSubmissionMail::class,
            fn (TemplatedFormSubmissionMail $mail) => count($mail->attachments()) === 1
        );

        Log::shouldHaveReceived('warning')->withArgs(
            fn (string $message, array $context) => $message === 'Form user mail attachments are missing'
                && $context['paths'] === ['attachments/gone.pdf']
        )->once();

        $submission->refresh();

        // The client got the letter, so the submission is not a failure — but the
        // gap has to be visible in the panel.
        $this->assertSame(FormSubmissionStatus::Sent, $submission->status);
        $this->assertStringContainsString('attachments/gone.pdf', (string) $submission->error_message);
        $this->assertStringNotContainsString('attachments/present.pdf', (string) $submission->error_message);
    }

    public function test_form_registers_its_mail_attachments_in_the_media_library(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('attachments/leaflet.pdf', 'data');

        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'send_user_mail' => true,
            'user_mail_attachments' => ['attachments/leaflet.pdf'],
        ]);

        // Without the usage record the media manager offers to delete the file as
        // unused, and the letter silently loses its attachment.
        $this->assertDatabaseHas('media_file_usages', [
            'usable_type' => $form->getMorphClass(),
            'usable_id' => $form->id,
            'field' => 'user_mail_attachments',
        ]);
    }
}
