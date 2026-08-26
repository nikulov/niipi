<?php

namespace App\Filament\Resources\Forms\Schemas;

use App\Mail\TemplatedFormSubmissionMail;
use App\Models\Form;
use App\Services\Forms\FormEmailTemplateRenderer;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Mail;

/**
 * Preview and test-send actions for the mail templates. Attached to the
 * matching section of {@see FormForm}, so the templates are read from the live
 * form state — the letter can be checked before the record is saved.
 *
 * $type is 'admin' or 'user' — it also prefixes the template field names.
 */
final class FormMailActions
{
    public static function preview(string $type): Action
    {
        $label = $type === 'admin'
            ? __('panel.preview_admin_mail')
            : __('panel.preview_user_mail');

        return Action::make("preview_{$type}_mail")
            ->label($label)
            ->color('gray')
            ->outlined()
            ->modalHeading($label)
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('panel.close'))
            ->visible(fn (?Form $record): bool => $record !== null)
            ->modalContent(function (Get $get, FormEmailTemplateRenderer $renderer) use ($type) {
                [$subject, $bodyTemplateMd] = self::templates($get, $type);

                if ($subject === '' || $bodyTemplateMd === '') {
                    return view('forms.email-preview', [
                        'error' => __('panel.email_template_is_empty'),
                        'subject' => '',
                        'html' => '',
                    ]);
                }

                return view('forms.email-preview', [
                    'error' => null,
                    'subject' => $subject,
                    'html' => $renderer->renderPreviewHtml($bodyTemplateMd),
                ]);
            });
    }

    public static function sendTest(string $type): Action
    {
        $label = $type === 'admin'
            ? __('panel.send_test_admin_mail')
            : __('panel.send_test_user_mail');

        return Action::make("send_test_{$type}_mail")
            ->label($label)
            ->color('gray')
            ->outlined()
            ->requiresConfirmation()
            ->visible(fn (?Form $record): bool => $record !== null)
            ->schema([
                TextInput::make('to')
                    ->label(__('panel.send_to'))
                    ->email()
                    ->required(),
            ])
            ->action(function (array $data, Get $get, FormEmailTemplateRenderer $renderer) use ($type) {
                [$subject, $bodyTemplateMd] = self::templates($get, $type);

                if ($subject === '' || $bodyTemplateMd === '') {
                    Notification::make()
                        ->title(__('panel.email_template_is_empty'))
                        ->danger()
                        ->send();

                    return;
                }

                Mail::to((string) $data['to'])->send(new TemplatedFormSubmissionMail(
                    $subject,
                    $renderer->renderPreviewHtml($bodyTemplateMd),
                    $bodyTemplateMd,
                ));

                Notification::make()
                    ->title(__('panel.test_email_sent'))
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array{0: string, 1: string} subject and body templates, as they
     *                                     currently stand in the form
     */
    private static function templates(Get $get, string $type): array
    {
        return [
            trim((string) $get("{$type}_mail_subject")),
            trim((string) $get("{$type}_mail_body_md")),
        ];
    }
}
