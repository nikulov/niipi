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

        $presenter = new PublicFormPresenter();
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
}
