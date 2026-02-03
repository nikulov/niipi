<?php

namespace Tests\Feature\Integration;

use App\Enums\FormSubmissionStatus;
use App\Jobs\SendFormSubmissionEmails;
use App\Livewire\Forms\PublicForm;
use App\Mail\AdminFormSubmissionMail;
use App\Mail\UserFormSubmissionMail;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FormSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_submission_dispatches_and_sends_emails_sync(): void
    {
        Config::set('queue.default', 'sync');
        Mail::fake();
        Storage::fake('public');

        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'is_active' => true,
            'recipient_admin_email' => 'admin@example.com',
            'send_admin_mail' => true,
            'send_user_mail' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'name',
            'label' => 'Name',
            'required' => true,
            'is_enabled' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'email',
            'name' => 'email',
            'label' => 'Email',
            'required' => true,
            'is_enabled' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'resume',
            'label' => 'Resume',
            'required' => true,
            'is_enabled' => true,
            'extra' => [
                'disk' => 'public',
                'accept_mimes' => ['application/pdf'],
                'max_size_kb' => 256,
            ],
        ]);

        Livewire::test(PublicForm::class, ['formId' => $form->id])
            ->set('data.name', 'Alice')
            ->set('data.email', 'user@example.com')
            ->set('uploads.resume', UploadedFile::fake()->create('resume.pdf', 10, 'application/pdf'))
            ->call('submit')
            ->assertSet('submitted', true);

        $submission = FormSubmission::query()->first();
        $this->assertNotNull($submission);
        $this->assertSame(FormSubmissionStatus::Sent, $submission->status);

        Mail::assertSent(AdminFormSubmissionMail::class, 1);
        Mail::assertSent(UserFormSubmissionMail::class, 1);
    }
}
