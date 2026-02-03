<?php

namespace Tests\Unit\Models;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormFieldTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_relation_and_casts(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        $field = FormField::create([
            'form_id' => $form->id,
            'type' => 'checkbox',
            'name' => 'agree',
            'label' => 'Agree',
            'required' => true,
            'is_enabled' => true,
            'rules' => ['boolean'],
            'options' => [['value' => 'yes', 'label' => 'Yes']],
            'extra' => ['multiple' => false],
        ]);

        $this->assertSame($form->id, $field->form->id);
        $this->assertIsBool($field->required);
        $this->assertIsBool($field->is_enabled);
        $this->assertIsArray($field->rules);
        $this->assertIsArray($field->options);
        $this->assertIsArray($field->extra);
    }
}
