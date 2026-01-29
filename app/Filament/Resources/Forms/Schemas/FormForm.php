<?php

namespace App\Filament\Resources\Forms\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    
                    Textarea::make('title')->label(__('panel.heading'))
                        ->rows(2)
                        ->autosize()
                        ->trim(),
                    
                    TextInput::make('name')->label(__('panel.name'))
                        ->required()
                        ->maxLength(255),
                    
                    TextInput::make('slug')->label(__('panel.slug'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    
                    TextInput::make('recipient_admin_email')->label(__('panel.recipient_admin_email'))
                        ->email()
                        ->required()
                        ->default('')
                        ->maxLength(255),
                    
                ])->columnSpan(12),
                
                Group::make()->schema([
                    
                    Toggle::make('is_active')->label(__('panel.is_active'))
                        ->default(true),
                    
                    Toggle::make('is_modal')->label(__('panel.is_modal'))
                        ->default(true),
                    
                    KeyValue::make('settings')->label(__('panel.settings'))
                    ->default([
                        'submit_label' => 'Send',
                        'success_message' => 'Message sent successfully!'
                    ]),
                    
                  
                
                ])->columnSpan(12),
            
            
            ])->columns(24);
    }
}
