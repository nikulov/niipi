<?php

namespace App\Filament\Resources\Forms\Schemas;

use App\Enums\FormApplicantType;
use App\Filament\Forms\Components\MediaPickerAction;
use App\Models\Form;
use App\Models\FormField;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([

                    TextInput::make('name')->label(__('panel.name'))
                        ->required()
                        ->maxLength(255),

                    Toggle::make('is_active')->label(__('panel.is_active'))
                        ->default(true),

                    Select::make('applicant_type')->label(__('panel.applicant_type'))
                        ->options(FormApplicantType::class)
                        ->required()
                        ->default(FormApplicantType::All->value),

                ])->columnSpan(12),

                Group::make()->schema([

                    Textarea::make('title')->label(__('panel.heading'))
                        ->rows(2)
                        ->autosize()
                        ->trim(),

                    Group::make()->schema([

                        TextInput::make('submit_label')->label(__('panel.btn_label'))
                            ->default('Отправить')
                            ->required(),

                    ])->statePath('settings'),

                ])->columnSpan(12),

                RichEditor::make('success_message')->label(__('panel.success_message'))
                    ->columnSpan(24)
                    ->resizableImages()
                    ->default('<p style="text-align: center;">Сообщение отправлено! Спасибо за обращение.</p>')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                        [
                            'h2',
                            'h3',
                            'highlight',
                            'horizontalRule',
                            'lead',
                            'alignStart',
                            'alignCenter',
                            'alignEnd',
                            'alignJustify',
                            'grid',
                            'gridDelete',
                        ],
                        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                        ['table', 'attachFiles'],
                        ['undo', 'redo'],
                    ]),

                Section::make(__('panel.emails'))
                    ->collapsed(true)
                    ->collapsible()
                    ->schema([

                        Section::make(__('panel.email_admin'))
                            ->headerActions([
                                FormMailActions::preview('admin'),
                                FormMailActions::sendTest('admin'),
                            ])
                            ->schema([

                                Toggle::make('send_admin_mail')
                                    ->live()
                                    ->label(__('panel.send_admin_mail')),

                                TextInput::make('recipient_admin_email')->label(__('panel.recipient_admin_email'))
                                    ->email()
                                    ->default('admin@niipigrad.ru')
                                    ->required(fn (Get $get): bool => (bool) $get('send_admin_mail'))
                                    ->maxLength(255),

                                TextInput::make('admin_mail_subject')->label(__('panel.email_subject'))
                                    ->trim()
                                    ->default('Новый заказ')
                                    ->required(fn (Get $get): bool => (bool) $get('send_admin_mail'))
                                    ->maxLength(255),

                                MarkdownEditor::make('admin_mail_body_md')->label(__('panel.email_body'))
                                    ->columnSpanFull()
                                    ->required(fn (Get $get): bool => (bool) $get('send_admin_mail')),

                                self::placeholdersSection('admin'),

                            ])
                            ->key('email-admin', isInheritable: false)
                            ->columnSpan(24),

                        Section::make(__('panel.email_user'))
                            ->headerActions([
                                FormMailActions::preview('user'),
                                FormMailActions::sendTest('user'),
                            ])
                            ->schema([

                                Toggle::make('send_user_mail')->label(__('panel.send_user_mail'))
                                    ->live()
                                    // Greyed out while the form has no email field: there would be
                                    // nowhere to take the recipient from.
                                    ->disabled(fn (?Form $record): bool => $record !== null && ! self::hasEmailField($record))
                                    // Shown off and saved off: a form that lost its email field
                                    // must not keep a switch that cannot work.
                                    ->afterStateHydrated(function (Toggle $component, ?Form $record): void {
                                        if ($record && ! self::hasEmailField($record)) {
                                            $component->state(false);
                                        }
                                    })
                                    ->dehydrated()
                                    ->helperText(fn (?Form $record): ?string => $record && ! self::hasEmailField($record)
                                        ? __('panel.form_has_no_email_field')
                                        : null)
                                    ->rules([
                                        fn (?Form $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                            // Without an email field there is nowhere to take the
                                            // recipient from — the letter would silently never go out.
                                            if ($value && $record && ! self::hasEmailField($record)) {
                                                $fail(__('panel.form_has_no_email_field'));
                                            }
                                        },
                                    ]),

                                TextInput::make('user_mail_subject')->label(__('panel.email_subject'))
                                    ->trim()
                                    ->required(fn (Get $get): bool => (bool) $get('send_user_mail'))
                                    ->maxLength(255),

                                MarkdownEditor::make('user_mail_body_md')
                                    ->label(__('panel.email_body'))
                                    ->required(fn (Get $get): bool => (bool) $get('send_user_mail'))
                                    ->columnSpanFull(),

                                self::placeholdersSection('user'),

                                FileUpload::make('user_mail_attachments')->label(__('panel.user_mail_attachments'))
                                    ->helperText(__('panel.user_mail_attachments_help'))
                                    ->disk('public')
                                    ->downloadable()
                                    ->openable()
                                    ->directory('forms/user-mail-attachments')
                                    ->multiple()
                                    ->preserveFilenames()
                                    ->downloadable()
                                    ->openable()
                                    ->panelLayout('grid')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'image/png',
                                        'image/jpeg',
                                    ])
                                    ->maxFiles(5)
                                    ->maxSize(10240) // 10 MB
                                    ->disabled(fn (Get $get): bool => ! (bool) $get('send_user_mail'))
                                    ->hintAction(MediaPickerAction::make('user_mail_attachments', multiple: true, acceptedMimeTypes: ['application/pdf', 'image/png', 'image/jpeg'], maxSize: 10240)),

                            ])
                            ->key('email-user', isInheritable: false)
                            ->columnSpan(24),
                    ])->columnSpanFull(),

            ])->columns(24);
    }

    /**
     * Placeholder => lang key of its description. Mirrors the context built by
     * {@see \App\Services\Forms\FormEmailTemplateRenderer::buildContext()},
     * minus `submission.status`: in a letter it is always `processing`.
     */
    private const DEFAULT_PLACEHOLDERS = [
        'form.name' => 'email_placeholder_form_name',
        'form.id' => 'email_placeholder_form_id',
        'submission.id' => 'email_placeholder_submission_id',
        'submission.created_at' => 'email_placeholder_submission_created_at',
        'submission.url' => 'email_placeholder_submission_url',
        'submission.ip' => 'email_placeholder_submission_ip',
        'submission.user_agent' => 'email_placeholder_submission_user_agent',
        'files' => 'email_placeholder_files',
    ];

    /**
     * Cheat sheet inside the collapsed section under the letter body: the static
     * context keys plus the form's own field names — their slugs are not visible
     * anywhere else in the panel.
     */
    public static function placeholdersHint(?Form $record): HtmlString
    {
        $defaults = [];

        foreach (self::DEFAULT_PLACEHOLDERS as $placeholder => $langKey) {
            $defaults[] = self::hintRow($placeholder, __('panel.'.$langKey));
        }

        $groups = [self::hintGroup(__('panel.email_placeholders_defaults'), $defaults)];

        $fields = $record === null
            ? collect()
            : $record->fields()->where('is_enabled', true)->orderBy('sort')->get();

        if ($fields->isNotEmpty()) {
            $rows = [];

            foreach ($fields as $field) {
                $rows[] = self::hintRow('field.'.$field->name, self::fieldDescription($field));
            }

            $groups[] = self::hintGroup(__('panel.email_placeholders_form_fields'), $rows);
        }

        $groups[] = '<div>'.e(__('panel.email_placeholders_unknown')).'</div>';

        return new HtmlString('<div style="display:grid;gap:0.75rem">'.implode('', $groups).'</div>');
    }

    /**
     * Layout goes through inline styles on purpose: the theme scans
     * `app/Filament/**` for classes, so utilities added here would stay dead
     * until the assets are rebuilt. Colours are left to inherit — otherwise the
     * hint would have to know about the dark theme.
     */
    private static function hintGroup(string $title, array $rows): string
    {
        return '<div style="display:grid;gap:0.25rem">'
            .'<div style="font-weight:600">'.e($title).'</div>'
            .implode('', $rows)
            .'</div>';
    }

    private static function hintRow(string $placeholder, string $description): string
    {
        return '<div><code style="font-family:ui-monospace,monospace">{{ '
            .e($placeholder)
            .' }}</code> — '
            .e($description)
            .'</div>';
    }

    /**
     * The label rather than the slug — but checkbox and radio labels are rich
     * text, so they need flattening before they fit on one line.
     */
    private static function fieldDescription(FormField $field): string
    {
        $label = trim(preg_replace('/\s+/', ' ', strip_tags((string) $field->label)));

        $description = $label === '' ? $field->name : Str::limit($label, 40);

        return $field->type === 'file'
            ? $description.' '.__('panel.email_placeholders_file_note')
            : $description;
    }

    private static function placeholdersSection(string $type): Section
    {
        return Section::make(__('panel.email_placeholders'))
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            // Explicit key: otherwise it is derived from the Russian heading by
            // transliteration.
            ->key('placeholders-'.$type, isInheritable: false)
            ->columnSpanFull()
            ->schema([
                Html::make(fn (?Form $record): HtmlString => self::placeholdersHint($record)),
            ]);
    }

    private static function hasEmailField(Form $form): bool
    {
        return $form->fields()
            ->where('is_enabled', true)
            ->where('type', 'email')
            ->exists();
    }
}
