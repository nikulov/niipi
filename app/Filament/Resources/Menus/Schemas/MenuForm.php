<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Filament\Forms\Components\CustomRepeater;
use App\Filament\Forms\Components\UrlInput;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;


class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('top_menu')->label(__(key: 'panel.top_menu'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        
                        CustomRepeater::make('top_items')->label(__(key: 'panel.top_menu_items'))
                            ->hiddenLabel()
                            ->maxItems(5)
                            ->columns(24)
                            ->columnSpanFull()
                            ->default([])
                            ->itemLabel(fn(array $state): string => $state['label'] ?? '')
                            ->reorderable(true)
                            ->collapsible()
                            ->collapsed()
                            ->reorderableWithButtons()
                            ->addActionLabel(__(key: 'panel.add_top_menu_item'))
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->schema([
                                
                                ...self::menuItemSchema(),
                                
                                CustomRepeater::make('children')->label(__('panel.sub_menu_items'))
                                    ->maxItems(5)
                                    ->columns(24)
                                    ->columnSpanFull()
                                    ->default([])
                                    ->itemLabel(fn(array $state): string => $state['label'] ?? '')
                                    ->reorderable(true)
                                    ->collapsible()
                                    ->collapsed()
                                    ->reorderableWithButtons()
                                    ->addActionLabel(__(key: 'panel.add_sub_menu_item'))
                                    ->deleteAction(
                                        fn(Action $action) => $action->requiresConfirmation(),
                                    )
                                    ->schema(self::menuItemSchema())
                            ])
                    ]),
                
                Fieldset::make('footer_menu')->label(__(key: 'panel.footer_menu'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        
                        CustomRepeater::make('footer_items')->label(__(key: 'panel.footer_menu_items'))
                            ->hiddenLabel()
                            ->maxItems(10)
                            ->columns(24)
                            ->columnSpanFull()
                            ->default([])
                            ->itemLabel(fn(array $state): string => $state['label'] ?? '')
                            ->reorderable(true)
                            ->collapsible()
                            ->collapsed()
                            ->reorderableWithButtons()
                            ->addActionLabel(__(key: 'panel.add_footer_menu_item'))
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->schema(self::menuItemSchema())
                    
                    ]),
            ]);
    }
    
    protected static function getPageOptions(): array
    {
        return Page::query()
            ->where('status', 'published')
            ->orderBy('title')
            ->pluck('title', 'slug')
            ->toArray();
    }
    
    
    protected static function menuItemSchema(): array
    {
        return [
            
            Select::make('type')->label(__('panel.type_link'))
                ->columnSpan(5)
                ->options([
                    'page' => 'Page',
                    'custom' => 'Custom URL',
                ])
                ->default('page')
                ->required()
                ->live(),
            
            Select::make('page_slug')->label(__(key: 'panel.page'))
                ->live()
                ->columnSpan(7)
                ->options(fn() => static::getPageOptions())
                ->searchable()
                ->preload()
                ->visible(fn(callable $get): bool => $get('type') === 'page')
                ->required(fn(callable $get): bool => $get('type') === 'page')
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    $title = static::getPageOptions()[$state] ?? null;
                    $set('label', $title);
                }),
            
            UrlInput::make('url')->label(__(key: 'panel.custom_url'))
                ->columnSpan(7)
                ->required()
                ->placeholder('/about')
                ->visible(fn(callable $get): bool => $get('type') === 'custom')
                ->required(fn(callable $get): bool => $get('type') === 'custom'),
            
            TextInput::make('label')->label(__(key: 'panel.label'))
                ->live()
                ->trim()
                ->columnSpan(7)
                ->required()
                ->maxLength(255),
            
            Toggle::make('blank')->label(__(key: 'panel.open_page_in_new_tab'))
                ->columnSpan(5)
                ->inline(false)
                ->default(false)
        ];
    }
}