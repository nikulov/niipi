<?php

namespace Tests\Feature\Filament;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionPageTest extends TestCase
{
    use RefreshDatabase;

    private function submission(?string $error): FormSubmission
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        return FormSubmission::create([
            'form_id' => $form->id,
            'status' => $error === null ? FormSubmissionStatus::Sent : FormSubmissionStatus::Failed,
            'data' => ['email' => 'user@example.com'],
            'error_message' => $error,
        ]);
    }

    public function test_page_shows_why_a_letter_did_not_arrive(): void
    {
        $submission = $this->submission('Письмо пользователю ушло без вложений — файлов нет в хранилище: forms/gone.pdf.');

        $this->actingAs($this->userOfRole(UserRole::Admin), 'web')
            ->get('/admin/form-submissions/'.$submission->id.'/edit')
            ->assertOk()
            ->assertSee('forms/gone.pdf');
    }

    public function test_page_hides_the_error_entry_when_there_is_nothing_to_report(): void
    {
        $submission = $this->submission(null);

        $this->actingAs($this->userOfRole(UserRole::Admin), 'web')
            ->get('/admin/form-submissions/'.$submission->id.'/edit')
            ->assertOk()
            ->assertDontSee(__('panel.error_message'));
    }
}
