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
            'rules' => ['min:3' => 'Слишком коротко'],
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

        $builder = new FormRulesBuilder;
        [$rules, $messages] = $builder->build($form);

        $this->assertSame(['required', 'min:3'], $rules['data.name']);
        $this->assertSame(['nullable', 'email'], $rules['data.email']);
        $this->assertSame(['nullable', 'boolean'], $rules['data.agree']);

        $this->assertSame('required', $rules['data.category'][0]);
        $inRule = collect($rules['data.category'])->first(fn ($rule) => $rule instanceof In);
        $this->assertNotNull($inRule);

        $this->assertSame('Слишком коротко', $messages['data.name.min']);
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
            'rules' => ['dimensions:min_width=10' => 'слишком узко'],
        ]);

        $builder = new FormRulesBuilder;
        [$rules, $messages] = $builder->build($form);

        $this->assertSame(
            ['required', 'file', 'mimetypes:application/pdf', 'max:256'],
            $rules['uploads.resume']
        );

        $this->assertSame(['nullable', 'array', 'max:2'], $rules['uploads.photos']);
        $this->assertSame(
            ['file', 'mimetypes:image/png', 'max:100', 'dimensions:min_width=10'],
            $rules['uploads.photos.*']
        );

        $this->assertSame('слишком узко', $messages['data.photos.dimensions']);
    }

    public function test_list_form_rules_are_applied_without_messages(): void
    {
        [$rules, $messages] = $this->buildFor(['min:3', 'max:10']);

        $this->assertSame(['nullable', 'min:3', 'max:10'], $rules['data.name']);
        $this->assertSame([], $messages);
    }

    public function test_mixed_form_keeps_both_shapes_and_leaks_no_integer_key(): void
    {
        [$rules, $messages] = $this->buildFor(['min:3', 'max:10' => 'Слишком длинно']);

        $this->assertSame(['nullable', 'min:3', 'max:10'], $rules['data.name']);
        $this->assertSame('Слишком длинно', $messages['data.name.max']);
        $this->assertArrayNotHasKey('data.name.min', $messages);
    }

    public function test_blank_and_non_string_rules_are_dropped(): void
    {
        [$rules, $messages] = $this->buildFor(['  ', 'min:3', 42, ['nested']]);

        $this->assertSame(['nullable', 'min:3'], $rules['data.name']);
        $this->assertSame([], $messages);
    }

    /** @return array{0: array<string, mixed>, 1: array<string, string>} */
    private function buildFor(array $fieldRules): array
    {
        $form = Form::create([
            'name' => 'rules-'.uniqid(),
            'title' => 'Rules',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'name',
            'label' => 'Name',
            'required' => false,
            'is_enabled' => true,
            'rules' => $fieldRules,
        ]);

        $form->load('fields');

        return (new FormRulesBuilder)->build($form);
    }
}
