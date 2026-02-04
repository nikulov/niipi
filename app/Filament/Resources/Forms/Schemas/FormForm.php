<?php

namespace App\Filament\Resources\Forms\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
                            'gridDelete'
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
                            
                            ])->columnSpan(24),
                        
                        Section::make(__('panel.email_user'))
                            ->schema([
                                
                                Toggle::make('send_user_mail')->label(__('panel.send_user_mail'))
                                    ->live(),
                                
                                TextInput::make('user_mail_subject')->label(__('panel.email_subject'))
                                    ->trim()
                                    ->required(fn (Get $get): bool => (bool) $get('send_user_mail'))
                                    ->maxLength(255),
                                
                                MarkdownEditor::make('user_mail_body_md')
                                    ->label(__('panel.email_body'))
                                    ->required(fn (Get $get): bool => (bool) $get('send_user_mail'))
                                    ->columnSpanFull(),
                                
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
                                    ->disabled(fn (Get $get): bool => ! (bool) $get('send_user_mail')),
                            
                            ])->columnSpan(24),
                    ])->columnSpanFull(),
            
            ])->columns(24);
    }
}
