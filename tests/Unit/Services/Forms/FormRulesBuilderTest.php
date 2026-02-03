<?php

namespace Tests\Unit\Services\Forms;

use App\Models\Form;
use App\Models\FormField;
use App\Services\Forms\FormRulesBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\In;
use Tests\TestCase;

class FormRulesBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_rules_for_common_types(): void
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
            'rules' => ['min:3'],
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'email',
            'name' => 'email',
            'label' => 'Email',
            'required' => false,
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
            'type' => 'select',
            'name' => 'category',
            'label' => 'Category',
            'required' => true,
            'is_enabled' => true,
            'options' => [
                ['label' => 'A', 'value' => 'a'],
                ['label' => 'B', 'value' => 'b'],
            ],
        ]);

        $builder = new FormRulesBuilder();
        $rules = $builder->build($form);

        $this->assertSame(['required', 'min:3'], $rules['data.name']);
        $this->assertSame(['nullable', 'email'], $rules['data.email']);
        $this->assertSame(['nullable', 'boolean'], $rules['data.agree']);

        $this->assertSame('required', $rules['data.category'][0]);
        $inRule = collect($rules['data.category'])->first(fn ($rule) => $rule instanceof In);
        $this->assertNotNull($inRule);
    }

    public function test_build_rules_for_file_fields(): void
    {
        $form = Form::create([
            'name' => 'files',
            'title' => 'Files',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'resume',
            'label' => 'Resume',
            'required' => true,
            'is_enabled' => true,
            'extra' => [
                'multiple' => false,
                'max_size_kb' => 256,
                'accept_mimes' => ['application/pdf'],
            ],
            'rules' => ['mimes:pdf'],
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'photos',
            'label' => 'Photos',
            'required' => false,
            'is_enabled' => true,
            'extra' => [
                'multiple' => true,
                'max_files' => 2,
                'max_size_kb' => 100,
                'accept_mimes' => ['image/png'],
            ],
            'rules' => ['dimensions:min_width=10'],
        ]);

        $builder = new FormRulesBuilder();
        $rules = $builder->build($form);

        $this->assertSame(
            ['required', 'file', 'mimetypes:application/pdf', 'max:256', 'mimes:pdf'],
            $rules['uploads.resume']
        );

        $this->assertSame(['nullable', 'array', 'max:2'], $rules['uploads.photos']);
        $this->assertSame(
            ['file', 'mimetypes:image/png', 'max:100', 'dimensions:min_width=10'],
            $rules['uploads.photos.*']
        );
    }
}
