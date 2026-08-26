<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\Forms\Pages\CreateForm;
use App\Filament\Resources\Forms\Pages\EditForm;
use App\Mail\TemplatedFormSubmissionMail;
use App\Models\Form;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class FormMailActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userOfRole(UserRole::Admin));
    }

    public function test_preview_action_renders_the_unsaved_template(): void
    {
        $form = Form::create(['name' => 'Обратная связь']);

        Livewire::test(EditForm::class, ['record' => $form->getRouteKey()])
            ->fillForm([
                'admin_mail_subject' => 'Новая заявка',
                'admin_mail_body_md' => 'Заявка с сайта',
            ])
            ->mountAction(TestAction::make('preview_admin_mail')->schemaComponent('email-admin'))
            ->assertMountedActionModalSee(['Новая заявка', 'Заявка с сайта']);
    }

    public function test_preview_action_reports_an_empty_template(): void
    {
        $form = Form::create(['name' => 'Обратная связь']);

        Livewire::test(EditForm::class, ['record' => $form->getRouteKey()])
            ->mountAction(TestAction::make('preview_user_mail')->schemaComponent('email-user'))
            ->assertMountedActionModalSee(__('panel.email_template_is_empty'));
    }

    public function test_test_mail_is_sent_with_the_unsaved_template(): void
    {
        Mail::fake();

        $form = Form::create(['name' => 'Обратная связь']);

        Livewire::test(EditForm::class, ['record' => $form->getRouteKey()])
            ->fillForm([
                'user_mail_subject' => 'Спасибо за обращение',
                'user_mail_body_md' => 'Мы свяжемся с вами',
            ])
            ->mountAction(TestAction::make('send_test_user_mail')->schemaComponent('email-user'))
            ->setActionData(['to' => 'qa@example.com'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        Mail::assertSent(
            TemplatedFormSubmissionMail::class,
            fn (TemplatedFormSubmissionMail $mail): bool => $mail->hasTo('qa@example.com')
                && $mail->envelope()->subject === 'Спасибо за обращение',
        );
    }

    public function test_mail_actions_are_hidden_while_the_form_is_being_created(): void
    {
        Livewire::test(CreateForm::class)
            ->assertActionDoesNotExist(TestAction::make('preview_admin_mail')->schemaComponent('email-admin'))
            ->assertActionDoesNotExist(TestAction::make('send_test_admin_mail')->schemaComponent('email-admin'));
    }
}
