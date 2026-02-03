<?php

namespace Tests\Unit\Services\Forms;

use App\Models\Form;
use App\Models\FormField;
use App\Services\Forms\FormValidationAttributesBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormValidationAttributesBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_attributes_for_enabled_fields(): void
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
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'resume',
            'label' => 'Resume',
            'is_enabled' => true,
            'extra' => ['multiple' => true],
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'hidden',
            'label' => 'Hidden',
            'is_enabled' => false,
        ]);

        $builder = new FormValidationAttributesBuilder();
        $attributes = $builder->build($form);

        $this->assertSame('Name', $attributes['data.name']);
        $this->assertSame('Resume', $attributes['uploads.resume']);
        $this->assertSame('Resume', $attributes['uploads.resume.*']);
        $this->assertArrayNotHasKey('data.hidden', $attributes);
    }
}
