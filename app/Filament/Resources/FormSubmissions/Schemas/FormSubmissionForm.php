<?php

namespace App\Filament\Resources\FormSubmissions\Schemas;

use App\Enums\FormSubmissionStatus;
use App\Jobs\SendFormSubmissionEmails;
use App\Presenters\Forms\FormSubmissionPresenter;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FormSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            
            Section::make(__('panel.general'))
                ->columns(24)
                ->columnSpanFull()
                ->hiddenLabel()
                ->schema([
                    
                    TextEntry::make('form_name')->label(__('panel.form'))
                        ->state(fn($record) => $record?->form?->name ?? '—')
                        ->columnSpan(6),
                    
                    TextEntry::make('created_at')->label(__('panel.created_at'))
                        ->state(fn($record) => $record?->created_at?->format('d.m.Y H:i') ?? '—')
                        ->columnSpan(6),
                    
                    Select::make('status')->label(__('panel.status'))
                        ->options(FormSubmissionStatus::options())
                        ->required()
                        ->columnSpan(6),
                    
                    Action::make('resend')->label(__('panel.resend'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->action(function ($record) {
                            $record->update([
                                'status' => FormSubmissionStatus::Processing,
                                'error_message' => null,
                            ]);
                            
                            SendFormSubmissionEmails::dispatch($record->id);
                        })
                        ->requiresConfirmation(),
                ]),
            
            Section::make(__('panel.data'))
                ->columnSpanFull()
                ->columns(24)
                ->schema([
                    
                    RepeatableEntry::make('data_rows')->label('')
                        ->columnSpanFull()
                        ->columns(24)
                        ->hiddenLabel()
                        ->state(fn($record) => app(FormSubmissionPresenter::class)->rows($record))
                        ->schema([
                            
                            TextEntry::make('label')->label(__('panel.field'))
                                ->hiddenLabel()
                                ->columnSpan(4),
                            
                            TextEntry::make('value')->label(__('panel.value'))
                                ->hiddenLabel()
                                ->columnSpan(12),
                        
                        ]),
                
                ]),
            
            Section::make(__('panel.files'))
                ->columnSpanFull()
                ->schema([
                    RepeatableEntry::make('files')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('original_name')->label(__('panel.file_name'))
                                ->columnSpan(6),
                            
                            TextEntry::make('mime_type')->label(__('panel.mime_type'))
                                ->columnSpan(6),
                            
                            TextEntry::make('size')->label(__('panel.size'))
                                ->columnSpan(6)
                                ->state(function ($record) {
                                    $bytes = (int)($record?->size ?? 0);
                                    
                                    return $bytes > 0
                                        ? number_format($bytes / 1024, 1) . ' KB'
                                        : '—';
                                }),
                            
                            
                            Action::make('download')->label(__('panel.download'))
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(function ($record) {
                                    if (!$record?->disk || !$record?->path) {
                                        return null;
                                    }
                                    
                                    return \Storage::disk($record->disk)->url($record->path);
                                }, true)
                                ->visible(fn($record) => filled($record?->disk) && filled($record?->path)),
                        
                        
                        ])
                        ->columns(24),
                ]),
            
            Section::make(__('panel.options'))
                ->columnSpanFull()
                ->columns(24)
                ->schema([
                    
                    TextEntry::make('ip')
                        ->label('IP')
                        ->state(fn($record) => $record?->ip ?? '—')
                        ->columnSpan(6),
                    
                    TextEntry::make('user_agent')
                        ->label('User-Agent')
                        ->state(fn($record) => $record?->user_agent ?? '—')
                        ->columnSpan(6),
                
                ]),
        
        
        ]);
    }
}