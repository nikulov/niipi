<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\CustomRepeater;
use App\Filament\Forms\Components\UrlInput;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


final class CardsBlockWithButton
{
    public static function key(): string
    {
        return 'cards-block-with-button';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.services_block'))
            ->columnSpanFull()
            ->schema([
                Textarea::make('title')->label(__(key: 'panel.title'))
                    ->maxLength(255)
                    ->trim()
                    ->columnSpanFull(),
                CustomRepeater::make('cards')->label('')
                    ->grid(3)
                    ->hiddenLabel()
                    ->itemLabel(fn(array $state): string => $state['title'] ?? __(key: 'panel.card'))
                    ->deleteAction(
                        fn(Action $action) => $action->requiresConfirmation(),
                    )
                    ->collapsed()
                    ->addActionLabel(__(key: 'panel.add_card'))
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('cardTitle')->label(__(key: 'panel.title'))
                            ->live(onBlur: true)
                            ->trim()
                            ->required(),
                        Textarea::make('cardDescription')->label(__(key: 'panel.description'))
                            ->required()
                            ->trim(),
                        TextInput::make('cardBtnLabel')->label(__(key: 'panel.btn_label'))
                            ->default(__(key: 'panel.more_details'))
                            ->required()
                            ->trim(),
                        UrlInput::make('cardBtnUrl')->label(__(key: 'panel.btn_url'))
                            ->placeholder(__(key: 'panel.url_placeholder'))
                            ->required(),
                    ]),
                TextInput::make('btnLabel')->label(__(key: 'panel.btn_label'))
                    ->columnSpan(12)
                    ->trim(),
                UrlInput::make('btnUrl')->label(__(key: 'panel.btn_url'))
                    ->columnSpan(12),
            ])->columns(24);
    }
}