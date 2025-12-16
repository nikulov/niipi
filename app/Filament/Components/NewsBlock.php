<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\UrlInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;


final class NewsBlock
{
    public static function key(): string
    {
        return 'news-block';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.news_block'))
            ->columnSpanFull()
            ->columns(12)
            ->schema([
                TextInput::make('title')->label(__(key: 'panel.title'))
                    ->columnSpan(3)
                    ->default(__(key: 'panel.news'))
                    ->required(),
                TextInput::make('quantity')->label(__(key: 'pane.quantity'))
                    ->columnSpan(1)
                    ->numeric()
                    ->default(4)
                    ->required(),
                TextInput::make('btn_label')->label(__(key: 'panel.btn_label'))
                    ->columnSpan(3)
                    ->default(__(key: 'panel.all_news'))
                    ->required(),
                UrlInput::make('btn_url')->label(__(key: 'panel.btn_url'))
                    ->columnSpan(5)
                    ->required()
                    ->default('news')
            ]);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.news-block';
    }
}