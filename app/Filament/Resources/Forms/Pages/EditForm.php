<?php

namespace App\Filament\Resources\Forms\Pages;

use App\Filament\Resources\Forms\FormResource;
use App\Mail\TemplatedFormSubmissionMail;
use App\Models\FormSubmission;
use App\Services\Forms\FormEmailTemplateRenderer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->action(fn () => $this->save()),
            DeleteAction::make(),
            
            //TODO add email actions
//            $this->previewMailAction('admin'),
//            $this->previewMailAction('user'),
//            $this->sendTestMailAction('admin'),
//            $this->sendTestMailAction('user'),
        ];
    }
    
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->action(fn () => $this->save()),
            DeleteAction::make(),
        ];
    }
    
    private function previewMailAction(string $type): Action
    {
        $label = $type === 'admin'
            ? __('panel.preview_admin_mail')
            : __('panel.preview_user_mail');

        return Action::make("preview_{$type}_mail")
            ->label($label)
            ->modalHeading($label)
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('panel.close'))
            ->modalContent(function (FormEmailTemplateRenderer $renderer) use ($type) {
                $form = $this->record;

                $submission = FormSubmission::query()
                    ->where('form_id', $form->id)
                    ->latest('id')
                    ->with(['form', 'files'])
                    ->first();

                if (! $submission) {
                    return view('forms.email-preview', [
                        'error' => __('panel.no_submissions_for_preview'),
                        'subject' => '',
                        'html' => '',
                    ]);
                }

                $subjectTemplate = $type === 'admin'
                    ? (string) ($form->admin_mail_subject ?? '')
                    : (string) ($form->user_mail_subject ?? '');

                $bodyTemplateMd = $type === 'admin'
                    ? (string) ($form->admin_mail_body_md ?? '')
                    : (string) ($form->user_mail_body_md ?? '');

                if (trim($subjectTemplate) === '' || trim($bodyTemplateMd) === '') {
                    return view('forms.email-preview', [
                        'error' => __('panel.email_template_is_empty'),
                        'subject' => '',
                        'html' => '',
                    ]);
                }

                $subject = $renderer->renderSubject($submission, $subjectTemplate);
                $html = $renderer->renderBodyHtml($submission, $bodyTemplateMd);

                return view('forms.email-preview', [
                    'error' => null,
                    'subject' => $subject,
                    'html' => $html,
                ]);
            });
    }

    private function sendTestMailAction(string $type): Action
    {
        $label = $type === 'admin'
            ? __('panel.send_test_admin_mail')
            : __('panel.send_test_user_mail');

        return Action::make("send_test_{$type}_mail")
            ->label($label)
            ->requiresConfirmation()
            ->schema([
                TextInput::make('to')
                    ->label(__('panel.send_to'))
                    ->email()
                    ->required(),
            ])
            ->action(function (array $data, FormEmailTemplateRenderer $renderer) use ($type) {
                $form = $this->record;

                $submission = FormSubmission::query()
                    ->where('form_id', $form->id)
                    ->latest('id')
                    ->with(['form', 'files'])
                    ->first();

                if (! $submission) {
                    Notification::make()
                        ->title(__('panel.no_submissions_for_preview'))
                        ->danger()
                        ->send();

                    return;
                }

                $subjectTemplate = $type === 'admin'
                    ? (string) ($form->admin_mail_subject ?? '')
                    : (string) ($form->user_mail_subject ?? '');

                $bodyTemplateMd = $type === 'admin'
                    ? (string) ($form->admin_mail_body_md ?? '')
                    : (string) ($form->user_mail_body_md ?? '');

                if (trim($subjectTemplate) === '' || trim($bodyTemplateMd) === '') {
                    Notification::make()
                        ->title(__('panel.email_template_is_empty'))
                        ->danger()
                        ->send();

                    return;
                }

                $subject = $renderer->renderSubject($submission, $subjectTemplate);
                $html = $renderer->renderBodyHtml($submission, $bodyTemplateMd);
                $text = $renderer->renderBodyText($submission, $bodyTemplateMd);

                Mail::to((string) $data['to'])->send(new TemplatedFormSubmissionMail($subject, $html, $text));

                Notification::make()
                    ->title(__('panel.test_email_sent'))
                    ->success()
                    ->send();
            });
    }
}
