<?php

namespace Tests\Unit\Actions;

use App\Actions\Forms\SubmitFormAction;
use App\Enums\FormSubmissionStatus;
use App\Jobs\SendFormSubmissionEmails;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmissionFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmitFormActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_creates_submission_stores_files_and_dispatches_job(): void
    {
        Bus::fake();
        Storage::fake('public');

        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
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
            'type' => 'checkbox',
            'name' => 'agree',
            'label' => 'Agree',
            'required' => false,
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

        $action = app(SubmitFormAction::class);

        $submission = $action->handle(
            $form,
            [
                'name' => 'Alice',
                'email' => 'user@example.com',
                'agree' => '1',
                'empty' => '   ',
            ],
            [
                'resume' => UploadedFile::fake()->create('resume.pdf', 10, 'application/pdf'),
            ],
            '127.0.0.1',
            'UA'
        );

        $this->assertSame(FormSubmissionStatus::Processing, $submission->status);
        $data = $submission->getAttribute('data');
        ksort($data);
        $this->assertSame(
            [
                'agree' => true,
                'email' => 'user@example.com',
                'name' => 'Alice',
            ],
            $data
        );

        $file = FormSubmissionFile::query()->first();
        $this->assertNotNull($file);
        Storage::disk('public')->assertExists($file->path);

        Bus::assertDispatched(SendFormSubmissionEmails::class, function (SendFormSubmissionEmails $job) use ($submission) {
            return $job->submissionId === $submission->id;
        });
    }

    public function test_handle_throws_when_rate_limited(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'name',
            'label' => 'Name',
            'required' => true,
            'is_enabled' => true,
        ]);

        $ip = '127.0.0.1';
        $key = sprintf('forms:%d:%s', $form->id, $ip);

        RateLimiter::clear($key);
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 300);
        }

        $action = app(SubmitFormAction::class);

        $this->expectException(ValidationException::class);

        $action->handle(
            $form,
            ['name' => 'Alice'],
            [],
            $ip,
            'UA'
        );
    }
}
