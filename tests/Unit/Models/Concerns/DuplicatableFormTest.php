<?php

namespace Tests\Unit\Models\Concerns;

use App\Enums\FormSubmissionStatus;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicatableFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_appends_kopiya_suffix_on_name_and_deactivates(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact us',
            'is_active' => true,
        ]);

        $copy = $form->duplicate();

        $this->assertSame('contact (копия)', $copy->name);
        $this->assertSame('Contact us', $copy->title);
        $this->assertFalse($copy->is_active);
    }

    public function test_duplicate_clones_fields_but_not_submissions(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'first_name',
            'label' => 'First name',
            'sort' => 1,
        ]);
        FormField::create([
            'form_id' => $form->id,
            'type' => 'email',
            'name' => 'email',
            'label' => 'Email',
            'sort' => 2,
        ]);
        FormSubmission::create([
            'form_id' => $form->id,
            'status' => FormSubmissionStatus::New,
            'data' => ['first_name' => 'X'],
        ]);

        $copy = $form->duplicate();

        $this->assertSame(2, $copy->fields()->count());
        $this->assertSame(
            ['first_name', 'email'],
            $copy->fields()->pluck('name')->all(),
        );
        $this->assertSame(0, $copy->submissions()->count());
    }

    public function test_duplicate_ignores_withcount_virtual_attributes(): void
    {
        $form = Form::create([
            'name' => 'wc',
            'title' => 'WC',
        ]);
        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'a',
            'label' => 'A',
            'sort' => 1,
        ]);

        $withCount = Form::query()
            ->withCount(['fields', 'submissions'])
            ->find($form->id);

        $this->assertSame(1, (int) $withCount->fields_count);

        $copy = $withCount->duplicate();

        $this->assertTrue($copy->exists);
        $this->assertSame('wc (копия)', $copy->name);
        $this->assertArrayNotHasKey('fields_count', $copy->getAttributes());
        $this->assertArrayNotHasKey('submissions_count', $copy->getAttributes());
    }

    public function test_repeated_duplicate_increments_counter(): void
    {
        $form = Form::create([
            'name' => 'x',
            'title' => 'X',
        ]);

        $form->duplicate();
        $form->duplicate();
        $third = $form->duplicate();

        $this->assertSame('x (копия 3)', $third->name);
    }
}
