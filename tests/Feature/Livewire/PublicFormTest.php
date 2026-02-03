<?php

namespace Tests\Feature\Livewire;

use App\Jobs\SendFormSubmissionEmails;
use App\Livewire\Forms\PublicForm;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PublicFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_mount_applies_select_and_radio_defaults(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'is_active' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'select',
            'name' => 'category',
            'label' => 'Category',
            'is_enabled' => true,
            'options' => [
                ['label' => 'A', 'value' => 'a', 'default' => true],
                ['label' => 'B', 'value' => 'b'],
            ],
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'radio',
            'name' => 'choice',
            'label' => 'Choice',
            'is_enabled' => true,
            'options' => [
                ['label' => 'X', 'value' => 'x'],
                ['label' => 'Y', 'value' => 'y', 'default' => true],
            ],
        ]);

        Livewire::test(PublicForm::class, ['formId' => $form->id])
            ->assertSet('data.category', 'a')
            ->assertSet('data.choice', 'y');
    }

    public function test_honeypot_field_skips_submission(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
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

        Livewire::test(PublicForm::class, ['formId' => $form->id])
            ->set('website', 'bot')
            ->set('data', ['name' => 'Alice'])
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSet('data', [])
            ->assertSet('uploads', []);

        $this->assertSame(0, FormSubmission::count());
    }

    public function test_submit_creates_submission_and_dispatches_job(): void
    {
        Bus::fake();
        Storage::fake('public');

        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
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
            ->assertSet('submitted', true)
            ->assertSet('data', [])
            ->assertSet('uploads', []);

        $this->assertSame(1, FormSubmission::count());
        Bus::assertDispatched(SendFormSubmissionEmails::class);
    }
}
