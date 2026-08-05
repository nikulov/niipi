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
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $html = Livewire::test(PublicForm::class, ['formId' => $form->id])
            ->assertSet('data.category', 'a')
            ->assertSet('data.choice', 'y')
            ->html();

        // разметка должна совпадать со state, иначе выбранное «на глаз» и отправленное расходятся
        $this->assertMatchesRegularExpression('/value="a"[^>]*selected/', $html);
        $this->assertMatchesRegularExpression('/value="y"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="x"[^>]*checked/', $html);
    }

    public function test_select_placeholder_passes_when_optional_and_blocks_when_required(): void
    {
        $optional = $this->formWithPlaceholderSelect(required: false);

        // «in:» — не implicit-правило, для пустой строки Laravel его пропускает
        Livewire::test(PublicForm::class, ['formId' => $optional->id])
            ->assertSet('data.topic', '')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $required = $this->formWithPlaceholderSelect(required: true);

        Livewire::test(PublicForm::class, ['formId' => $required->id])
            ->assertSet('data.topic', '')
            ->call('submit')
            ->assertHasErrors('data.topic')
            ->assertSet('submitted', false);
    }

    private function formWithPlaceholderSelect(bool $required): Form
    {
        $form = Form::create([
            'name' => 'placeholder-'.($required ? 'required' : 'optional'),
            'title' => 'Contact',
            'is_active' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'select',
            'name' => 'topic',
            'label' => 'Topic',
            'required' => $required,
            'is_enabled' => true,
            'options' => [
                ['label' => 'Выберите тему', 'value' => '', 'disabled' => true, 'default' => true],
                ['label' => 'ПЗЗ', 'value' => 'pzz'],
            ],
        ]);

        return $form;
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

    /**
     * Чексумма Livewire покрывает снапшот, но не карту `updates` — без
     * #[Locked] клиент переписывал viewData и ронял рендер шаблона.
     *
     * @return list<array{0: string, 1: mixed}>
     */
    public static function lockedPropertiesProvider(): array
    {
        return [
            'viewData' => ['viewData', []],
            'submitted' => ['submitted', true],
            'componentKey' => ['componentKey', 'spoofed'],
        ];
    }

    #[DataProvider('lockedPropertiesProvider')]
    public function test_server_owned_properties_reject_client_updates(string $property, mixed $value): void
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
            'is_enabled' => true,
        ]);

        $component = Livewire::test(PublicForm::class, ['formId' => $form->id]);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        $component->set($property, $value);
    }
}
