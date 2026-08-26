<?php

namespace Tests\Unit\Presenters\Forms;

use App\Models\Form;
use App\Models\FormField;
use App\Presenters\Forms\PublicFormPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFormPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_present_builds_view_data(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
            'settings' => [
                'submit_label' => 'Send It',
            ],
            'success_message' => 'Thanks',
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
            'type' => 'file',
            'name' => 'resume',
            'label' => 'Resume',
            'is_enabled' => true,
            'extra' => [
                'multiple' => true,
                'max_files' => 2,
                'max_size_kb' => 100,
                'accept_mimes' => ['application/pdf'],
            ],
        ]);

        $form->load('fields');

        $presenter = new PublicFormPresenter;
        $data = $presenter->present($form);

        $this->assertSame('Contact', $data['title']);
        $this->assertSame('Send It', $data['submitLabel']);
        $this->assertSame('Thanks', $data['successMessage']);
        $this->assertFalse($data['isModal']);

        $select = $data['fields'][0];
        $this->assertSame('select', $select['type']);
        $this->assertSame('a', $select['default']);

        $file = $data['fields'][1];
        $this->assertSame('file', $file['type']);
        $this->assertTrue($file['file']['multiple']);
        $this->assertSame(2, $file['file']['maxFiles']);
        $this->assertSame(100, $file['file']['maxSizeKb']);
        $this->assertSame('application/pdf', $file['file']['acceptAttr']);
    }

    public function test_select_keeps_disabled_placeholder_as_default(): void
    {
        $field = $this->presentSingleField('select', [
            ['label' => 'Выберите тему', 'value' => '', 'disabled' => true, 'default' => true],
            ['label' => 'A', 'value' => 'a'],
        ]);

        $this->assertCount(2, $field['options']);
        $this->assertSame('', $field['options'][0]['value']);
        $this->assertTrue($field['options'][0]['disabled']);
        $this->assertTrue($field['options'][0]['default']);
        $this->assertSame('', $field['default']);
    }

    public function test_radio_drops_empty_value_placeholder(): void
    {
        $field = $this->presentSingleField('radio', [
            ['label' => 'Выберите тему', 'value' => '', 'disabled' => true, 'default' => true],
            ['label' => 'Да', 'value' => 'yes'],
        ]);

        $this->assertCount(1, $field['options']);
        $this->assertSame('yes', $field['options'][0]['value']);
        $this->assertNull($field['default']);
    }

    public function test_only_first_default_option_wins(): void
    {
        $field = $this->presentSingleField('select', [
            ['label' => 'A', 'value' => 'a', 'default' => true],
            ['label' => 'B', 'value' => 'b', 'default' => true],
        ]);

        $this->assertTrue($field['options'][0]['default']);
        $this->assertFalse($field['options'][1]['default']);
        $this->assertSame('a', $field['default']);
    }

    public function test_zero_string_value_can_be_default(): void
    {
        $field = $this->presentSingleField('select', [
            ['label' => 'Нет', 'value' => '0', 'default' => true],
            ['label' => 'Да', 'value' => '1'],
        ]);

        $this->assertSame('0', $field['default']);
    }

    private function presentSingleField(string $type, array $options): array
    {
        $form = Form::create([
            'name' => 'options-'.uniqid(),
            'title' => 'Options',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => $type,
            'name' => 'topic',
            'label' => 'Topic',
            'is_enabled' => true,
            'options' => $options,
        ]);

        $form->load('fields');

        return (new PublicFormPresenter)->present($form)['fields'][0];
    }
}
