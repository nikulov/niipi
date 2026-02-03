<?php

namespace Tests\Unit\Presenters\Forms;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Presenters\Forms\FormSubmissionPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_rows_formats_values_and_respects_order(): void
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
            'is_enabled' => true,
            'sort' => 2,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'checkbox',
            'name' => 'agree',
            'label' => 'Agree',
            'is_enabled' => true,
            'sort' => 1,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'hidden',
            'label' => 'Hidden',
            'is_enabled' => false,
            'sort' => 3,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'tags',
            'label' => 'Tags',
            'is_enabled' => true,
            'sort' => 4,
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => 'new',
            'data' => [
                'name' => 'Alice',
                'agree' => true,
                'hidden' => 'secret',
                'tags' => ['a', 'b'],
            ],
        ]);

        $submission->setRelation('form', $form->load('fields'));

        $presenter = new FormSubmissionPresenter();
        $rows = $presenter->rows($submission);

        $this->assertSame('Agree', $rows[0]['label']);
        $this->assertSame(__('panel.yes'), $rows[0]['value']);

        $this->assertSame('Name', $rows[1]['label']);
        $this->assertSame('Alice', $rows[1]['value']);

        $this->assertSame('Tags', $rows[2]['label']);
        $this->assertSame('["a","b"]', $rows[2]['value']);
    }
}
