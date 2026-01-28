<?php

namespace App\Filament\Resources\Forms\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';
    
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()->schema([
                
                Select::make('type')->label(__('panel.field_type'))
                    ->options([
                        'text' => __('panel.text'),
                        'email' => __('panel.email'),
                        'phone' => __('panel.phone'),
                        'textarea' => __('panel.textarea'),
                        'file' => __('panel.file'),
                        'select' => __('panel.select'),
                        'checkbox' => __('panel.checkbox'),
                        'radio' => __('panel.radio'),
                    ])
                    ->required()
                    ->reactive()
                    ->disabled(fn(?Model $record) => filled($record)),
                
                TextInput::make('label_text')->label(__('panel.field_label'))
                    ->trim()
                    ->visible(fn (callable $get) => ! in_array($get('type'), ['checkbox', 'radio'], true))
                    ->required(fn (callable $get) => ! in_array($get('type'), ['checkbox', 'radio'], true))
                    ->dehydrated(fn (callable $get) => ! in_array($get('type'), ['checkbox', 'radio'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (callable $set, $state): void {
                        $set('label', $state);
                    })
                    ->afterStateHydrated(function (callable $set, ?Model $record): void {
                        if (! $record) {
                            return;
                        }
                        
                        if (! in_array($record->type, ['checkbox', 'radio'], true)) {
                            $set('label_text', $record->label);
                            $set('label', $record->label);
                        }
                    }),
                
                RichEditor::make('label_md')->label(__('panel.field_label'))
                    ->visible(fn (callable $get) => in_array($get('type'), ['checkbox', 'radio'], true))
                    ->required(fn (callable $get) => in_array($get('type'), ['checkbox', 'radio'], true))
                    ->dehydrated(fn (callable $get) => in_array($get('type'), ['checkbox', 'radio'], true))
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (callable $set, $state): void {
                        $set('label', $state);
                    })
                    ->afterStateHydrated(function (callable $set, ?Model $record): void {
                        if (! $record) {
                            return;
                        }
                        
                        if (in_array($record->type, ['checkbox', 'radio'], true)) {
                            $set('label_md', $record->label);
                            $set('label', $record->label);
                        }
                    })
                    ->toolbarButtons([['bold', 'link']]),
                
                Hidden::make('label')
                    ->dehydrated(true)
                    ->required(),
                
                TextInput::make('placeholder')->label(__('panel.field_placeholder'))
                    ->maxLength(255)
                    ->visible(fn(callable $get) => in_array($get('type'), ['text', 'email', 'phone', 'textarea'], true)
                    ),
                
                TextInput::make('name')->label(__('panel.field_name'))
                    ->required()
                    ->maxLength(255)
                    ->trim()
                    ->regex('/^[a-z][a-z0-9_]*$/')
                    ->helperText(__('panel.field_name_help')),
                
                
                CodeEditor::make('options')->label(__('panel.options'))
                    ->language(CodeEditor\Enums\Language::Json)
                    ->helperText(__('panel.options_help'))
                    ->visible(fn(callable $get) => in_array($get('type'), ['select', 'radio'], true))
                    ->json()
                    ->dehydrateStateUsing(fn($state) => is_string($state) ? json_decode($state, true) : $state)
                    ->formatStateUsing(fn($state) => is_array($state)
                        ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                        : $state
                    ),
            
            ])->columnSpan(12),
            
            
            Group::make()->schema([
                Group::make()->schema([
                    
                    Toggle::make('is_enabled')->label(__('panel.is_active'))
                        ->default(true),
                    
                    Toggle::make('required')->label(__('panel.required'))
                        ->default(false),
                
                ])->columnSpan(12),
                
                
                CodeEditor::make('rules')->label(__('panel.validation_rules'))
                    ->language(CodeEditor\Enums\Language::Json)
                    ->helperText(__('panel.rules_help'))
                    ->visible(fn(callable $get) => !in_array($get('type'), ['checkbox', 'radio'], true))
                    ->json()
                    ->dehydrateStateUsing(fn($state) => is_string($state) ? json_decode($state, true) : $state)
                    ->formatStateUsing(fn($state) => is_array($state)
                        ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                        : $state
                    )
                    ->columnSpan(12),
                
                
                CodeEditor::make('extra')->label(__('panel.extra'))
                    ->language(CodeEditor\Enums\Language::Json)
                    ->helperText(__('panel.extra_help'))
                    ->visible(fn(callable $get) => $get('type') === 'file')
                    ->default([
                        'multiple' => true,
                        'max_files' => 5,
                        'max_size_kb' => 5120,
                        'accept_mimes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                    ])
                    ->json()
                    ->dehydrateStateUsing(fn($state) => is_string($state) ? json_decode($state, true) : $state)
                    ->formatStateUsing(fn($state) => is_array($state)
                        ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                        : $state
                    )
                    ->columnSpan(12),
            
            ])->columnSpan(12),
        
        ])->columns(24);
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                
                TextColumn::make('label')->label(__('panel.label'))
                    ->html()
                    ->wrap()
                    ->formatStateUsing(function ($state) {
                        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $state)));
                        return Str::limit($text, 16);
                    }),
                
                TextColumn::make('name')->label(__('panel.name'))
                    ->copyable()
                    ->limit(10)
                    ->wrap()
                    ->icon('heroicon-o-document-duplicate'),
                
                TextColumn::make('type')->label(__('panel.type'))
                    ->badge()
                    ->sortable(),
                
                IconColumn::make('required')->label(__('panel.required'))
                    ->boolean(),
                
                IconColumn::make('is_enabled')->label(__('panel.is_active'))
                    ->boolean(),
            
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        if (!isset($data['sort'])) {
                            $ownerId = $this->getOwnerRecord()->getKey();
                            
                            $maxSort = (int)\DB::table('form_fields')
                                ->where('form_id', $ownerId)
                                ->max('sort');
                            
                            $data['sort'] = $maxSort + 1;
                        }
                        
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
