<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\Forms\Pages\EditForm;
use App\Filament\Resources\Forms\Schemas\FormForm;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormMailPlaceholdersHintTest extends TestCase
{
    use RefreshDatabase;

    public function test_hint_lists_the_enabled_fields_of_the_form(): void
    {
        $form = Form::create(['name' => 'Обратная связь']);

        $form->fields()->createMany([
            ['type' => 'email', 'name' => 'email', 'label' => 'E-mail', 'is_enabled' => true, 'sort' => 1],
            ['type' => 'file', 'name' => 'cv', 'label' => 'Резюме', 'is_enabled' => true, 'sort' => 2],
            ['type' => 'text', 'name' => 'hidden_one', 'label' => 'Скрытое', 'is_enabled' => false, 'sort' => 3],
        ]);

        $hint = FormForm::placeholdersHint($form)->toHtml();

        $this->assertStringContainsString('{{ files }}', $hint);
        $this->assertStringContainsString('{{ field.email }}', $hint);
        $this->assertStringContainsString('{{ field.cv }}', $hint);
        $this->assertStringContainsString(__('panel.email_placeholders_file_note'), $hint);
        $this->assertStringNotContainsString('hidden_one', $hint);
    }

    public function test_hint_is_rendered_on_the_edit_page(): void
    {
        $form = Form::create(['name' => 'Обратная связь']);

        $form->fields()->create([
            'type' => 'file', 'name' => 'cv', 'label' => 'Резюме', 'is_enabled' => true, 'sort' => 1,
        ]);

        $this->actingAs($this->userOfRole(UserRole::Admin));

        Livewire::test(EditForm::class, ['record' => $form->getRouteKey()])
            ->assertSee('{{ field.cv }}', escape: false)
            ->assertSee('{{ files }}', escape: false);
    }

    public function test_hint_keeps_the_static_part_without_a_record(): void
    {
        $hint = FormForm::placeholdersHint(null)->toHtml();

        $this->assertStringContainsString('{{ files }}', $hint);
        $this->assertStringNotContainsString(__('panel.email_placeholders_form_fields'), $hint);
    }
}
