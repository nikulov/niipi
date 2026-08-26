<?php

namespace Tests\Feature\Livewire;

use App\Actions\Forms\SubmitFormAction;
use App\Jobs\SendFormSubmissionEmails;
use App\Livewire\Forms\PublicForm;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use Facades\Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use League\Flysystem\PathTraversalDetected;
use League\Flysystem\UnableToRetrieveMetadata;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/**
 * Bug #23 — a 500 when the public form is submitted with a file.
 * Analysis and plan: .ai/plans/public-form-crashes/README.md
 */
class PublicFormUploadCrashTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Layer 1. The client sends an empty path to `_finishUpload`, it becomes
     * `livewire-tmp`, and hydration of the next request makes it
     * `livewire-tmp/livewire-tmp`. The `max:` rule used to blow the whole
     * request up on `size()`.
     */
    public function test_dead_upload_path_becomes_validation_error(): void
    {
        $form = $this->formWithFileField();

        Livewire::test(PublicForm::class, ['formId' => $form->id])
            ->set('data.name', 'Alice')
            ->call('_finishUpload', 'uploads.attachment', [''], true)
            ->call('submit')
            ->assertHasErrors('uploads.attachment')
            ->assertSet('submitted', false)
            // the dead file is gone from the state, otherwise a retry would
            // hit exactly the same wall
            ->assertSet('uploads.attachment', []);

        $this->assertSame(0, FormSubmission::count());
    }

    /**
     * A field error is what the visitor gets; the log is what we get. Without
     * it a broken disk stays invisible: layer 2 never runs, because layer 1
     * throws first.
     */
    public function test_dropped_file_is_logged(): void
    {
        $records = [];

        Log::listen(function ($message) use (&$records) {
            $records[] = $message->level.': '.$message->message;
        });

        $form = $this->formWithFileField();

        Livewire::test(PublicForm::class, ['formId' => $form->id])
            ->set('data.name', 'Alice')
            ->call('_finishUpload', 'uploads.attachment', [''], true)
            ->call('submit')
            ->assertHasErrors('uploads.attachment');

        $this->assertCount(1, $records);
        $this->assertStringContainsString('warning: public form dropped temporary files', $records[0]);
    }

    /**
     * Bug #42. `TemporaryUploadedFile::storeAs()` discards what `put()`
     * returned, so a failed write used to produce a healthy-looking row
     * pointing at a file that was never written.
     */
    public function test_failed_store_does_not_create_a_submission(): void
    {
        Bus::fake();

        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('put')->andReturnFalse();
        $disk->shouldReceive('exists')->andReturnFalse();
        Storage::set('public', $disk);

        $form = $this->formWithFileField();

        Livewire::test(PublicForm::class, ['formId' => $form->id])
            ->set('data.name', 'Alice')
            ->set('uploads.attachment', [UploadedFile::fake()->create('report.pdf', 10, 'application/pdf')])
            ->call('submit')
            ->assertHasErrors('uploads.attachment')
            ->assertSet('submitted', false);

        $this->assertSame(0, FormSubmission::count());
        $this->assertSame(0, FormSubmissionFile::count());
        Bus::assertNotDispatched(SendFormSubmissionEmails::class);
    }

    /**
     * Layer 2. A file that passes the filtering (`exists()` is true) but cannot
     * be read on `size()`: validation owes a form error, not an exception.
     */
    public function test_unreadable_file_becomes_validation_error(): void
    {
        $form = $this->formWithFileField();

        $file = Mockery::mock(TemporaryUploadedFile::class);
        $file->shouldReceive('exists')->andReturnTrue();
        $file->shouldReceive('isValid')->andReturnTrue();
        $file->shouldReceive('getPath')->andReturn('livewire-tmp');
        $file->shouldReceive('getClientOriginalExtension')->andReturn('pdf');
        $file->shouldReceive('getMimeType')->andReturn('application/pdf');
        $file->shouldReceive('getSize')->andThrow(
            UnableToRetrieveMetadata::fileSize('livewire-tmp/livewire-tmp')
        );

        try {
            app(SubmitFormAction::class)->handle($form, ['name' => 'Alice'], ['attachment' => [$file]], null, null);

            $this->fail('expected a ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('form', $e->errors());
        }

        $this->assertSame(0, FormSubmission::count());
    }

    /**
     * Link 1: where the empty path comes from. On a failed write `storeAs()`
     * returns `false` (the disk has `throw => false`), `str_replace()` in
     * FileUploadController coerces that to `''` — and the client gets a 200
     * with an empty path instead of an error. The JS passes `response.paths`
     * into `_finishUpload` as is.
     */
    public function test_failed_store_answers_200_with_empty_path(): void
    {
        // a disk whose write failed: with `throw => false` this is exactly how
        // `put()` answers — `false` instead of an exception
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('putFileAs')->andReturnFalse();
        $disk->shouldReceive('allFiles')->andReturn([]);

        Storage::set(FileUploadConfiguration::disk(), $disk);

        $response = $this->post(
            GenerateSignedUploadUrl::forLocal(),
            ['files' => [UploadedFile::fake()->create('report.pdf', 10, 'application/pdf')]],
            ['Accept' => 'application/json'],
        );

        $response->assertOk();
        $this->assertSame('{"paths":[""]}', $response->getContent());
    }

    /**
     * The temporary file path comes from the client, so it can also be aimed.
     * On `..` Flysystem throws `PathTraversalDetected` in the
     * `TemporaryUploadedFile` constructor, i.e. inside vendor's
     * `_finishUpload` — our code is never reached and the filtering layers have
     * nothing to do with it. That is a 500 on the upload request; we live with
     * it deliberately, same as with `CannotUpdateLockedProperty` in #24. What
     * matters here is the other half: the component state stays clean, so a
     * submission after such an attempt is not poisoned.
     */
    public function test_traversal_path_is_refused_inside_livewire(): void
    {
        $form = $this->formWithFileField();

        $component = Livewire::test(PublicForm::class, ['formId' => $form->id]);

        try {
            $component->call('_finishUpload', 'uploads.attachment', ['../../.env'], true);

            $this->fail('expected a PathTraversalDetected');
        } catch (PathTraversalDetected) {
            // refused before our code runs — that is the point of the test
        }

        $this->assertSame([], $component->get('uploads'));
    }

    private function formWithFileField(): Form
    {
        $form = Form::create([
            'name' => 'contacts',
            'title' => 'Contacts',
            'is_active' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'name',
            'label' => 'Name',
            'required' => true,
            'is_enabled' => true,
        ]);

        // field configuration matching all 13 forms in production
        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'attachment',
            'label' => 'Attachment',
            'is_enabled' => true,
            'extra' => [
                'disk' => 'public',
                'multiple' => true,
                'max_files' => 5,
                'max_size_kb' => 5120,
                'accept_mimes' => ['application/pdf', 'image/jpeg', 'image/png'],
            ],
        ]);

        return $form;
    }
}
