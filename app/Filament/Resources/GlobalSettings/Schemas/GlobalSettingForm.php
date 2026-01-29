<?php

namespace App\Filament\Resources\GlobalSettings\Schemas;

use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;


class GlobalSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('settings')->label(__('panel.settings'))
                    ->columns(24)
                    ->columnSpanFull()
                    ->schema([
                        
                        TextInput::make('title')->label(__(key: 'panel.site-title'))
                            ->trim()
                            ->columnSpan(24),
                        
                        TextInput::make('description')->label(__(key: 'panel.site-description'))
                            ->trim()
                            ->columnSpan(24),
                        
                        TextInput::make('email')->label(__(key: 'panel.admin-email'))
                            ->trim()
                            ->columnSpan(12),

//                        FileUpload::make('favicon')->label(__(key: 'panel.favicon'))
//                            ->columnSpan(12)
//                            ->image()
//                            ->imageEditor()
                    ]),
                
                Fieldset::make('code')->label(__('panel.code'))
                    ->columns(24)
                    ->columnSpanFull()
                    ->schema([
                        
                        CodeEditor::make('code_header')->label(__('panel.code_header'))
                            ->columnSpan(24)
                            ->language(CodeEditor\Enums\Language::Html),
                        
                        CodeEditor::make('code_body_top')->label(__('panel.code_body_top'))
                            ->columnSpan(24)
                            ->language(CodeEditor\Enums\Language::Html),
                        
                        CodeEditor::make('code_body_bottom')->label(__('panel.code_body_bottom'))
                            ->columnSpan(24)
                            ->language(CodeEditor\Enums\Language::Html),
                    ]),
            ]);
    }
}
