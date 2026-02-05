<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\CustomRepeater;
use App\Filament\Forms\Components\UrlInput;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;

final class Accordion
{
    public static function key(): string
    {
        return 'accordion';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.accordion'))
            ->columnSpanFull()
            ->schema([
                
                Group::make([
                    
                    Textarea::make('title')->label(__(key: 'panel.title'))
                        ->maxLength(255)
                        ->autosize()
                        ->trim()
                        ->columnSpan(20),
                    
                    Select::make('type')->label(__(key: 'panel.type'))
                        ->required()
                        ->columnSpan(4)
                        ->default('white')
                        ->options([
                            'white' => __('panel.white'),
                            'dark' => __('panel.dark'),
                        ]),
                    
                ])->columnSpanFull()->columns(24),
                
                CustomRepeater::make('accordions')->label('')
                    ->deleteAction(
                        fn(Action $action) => $action->requiresConfirmation(),
                    )
                    ->label('')
                    ->maxItems(20)
                    ->hiddenLabel()
                    ->itemLabel(fn(array $state): string => $state['title'] ?? __('panel.accordion'))
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->addActionLabel(__(key: 'panel.add_accordion'))
                    ->columnSpanFull()
                    ->columns(24)
                    ->collapsed()
                    ->reorderable()
                    ->schema([
                        
                        Group::make([
                            
                            TextInput::make('point')->label(__(key: 'panel.point'))
                                ->columnSpan(4)
                                ->trim()
                                ->maxLength(20),
                            
                            TextInput::make('itemTitle')->label(__('panel.title'))
                                ->columnSpan(12)
                                ->trim()
                                ->maxLength(255)
                                ->required()
                                ->live(onBlur: true),
                            
                            TextInput::make('itemDescription')->label(__(key: 'panel.description'))
                                ->columnSpan(8)
                                ->trim()
                                ->maxLength(255),
                        ])
                            ->columnSpanFull()
                            ->columns(24),
                        
                        Builder::make('items')->label(__(key: 'panel.body'))
                            ->columnSpanFull()
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation()
                            )
                            ->hiddenLabel()
                            ->reorderableWithButtons()
                            ->cloneable()
                            ->schema([
                                
                                Block::make('question')->label(__('panel.question'))
                                    ->columnSpanFull()
                                    ->schema(self::getBlocks())
                                    ->columns(24),
                                
                                Block::make('plus')->label(__('panel.plus'))
                                    ->columnSpanFull()
                                    ->schema(self::getBlocks())
                                    ->columns(24),
                                
                                Block::make('info')->label(__('panel.info'))
                                    ->columnSpanFull()
                                    ->schema(self::getBlocks())
                                    ->columns(24),
                            ])
                    ]),
            
            ]);
    }
    
    public static function getBlocks(): array
    {
        return [
            
            TextInput::make('title')->label(__('panel.title'))
                ->required()
                ->trim()
                ->maxLength(255)
                ->columnSpanFull(),
            
            RichEditor::make('description')->label(__('panel.description'))
                ->columnSpanFull()
                ->required()
                ->toolbarButtons([
                    ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                    [
                        'h2',
                        'h3',
                        'highlight',
                        'horizontalRule',
                        'alignStart',
                        'alignCenter',
                        'alignEnd',
                        'grid'
                    ],
                    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                    ['table', 'attachFiles'],
                    ['undo', 'redo'],
                ]),
            
            TextInput::make('btnLabel')->label(__('panel.btn_label'))
                ->maxLength(255)
                ->trim()
                ->columnSpan(8),
            
            UrlInput::make('btnUrl')->label(__('panel.btn_url'))
                ->columnSpan(10),
            
            Toggle::make('blank')->label(__(key: 'panel.open_page_in_new_tab'))
                ->inline(false)
                ->default(false)
                ->columnSpan(6),
        ];
    }
}