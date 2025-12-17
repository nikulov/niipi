<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageStatus;
use App\Filament\Components\BlockRegistry;
use App\Filament\Forms\Components\UrlInput;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;


class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('settings')->label(__('panel.settings'))
                    ->columns(24)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpan(9),
                        UrlInput::make('slug')->label('Slug')
                            ->unique(Page::class, 'slug', ignoreRecord: true)
                            ->required()
                            ->columnSpan(9),
                        Select::make('status')
                            ->columnSpan(6)
                            ->required()
                            ->options(PageStatus::class)
                            ->default(PageStatus::Draft),
                    ]),
                Fieldset::make('top_items')->label(__('Top section'))
                    ->columnSpanFull()
                    ->schema([
                        Builder::make('top_section')->label('')
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->hiddenLabel()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->blocks(BlockRegistry::topSection())
                    ]),
                Fieldset::make('main_items')->label(__('Main section'))
                    ->columnSpanFull()
                    ->schema([
                        Builder::make('main_section')->label('')
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->hiddenLabel()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->blocks(BlockRegistry::mainSection())
                    ]),
                Fieldset::make('bottom_items')->label(__('Bottom section'))
                    ->columnSpanFull()
                    ->schema([
                        Builder::make('bottom_section')->label('')
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->hiddenLabel()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->blocks(BlockRegistry::bottomSection())
                    ])
            ]);
    }
}
