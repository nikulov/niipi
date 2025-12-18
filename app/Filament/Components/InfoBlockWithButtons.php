<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\UrlInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;


final class InfoBlockWithButtons
{
    public static function key(): string
    {
        return 'info-block-with-buttons';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.info-block-with-buttons'))
            ->columnSpanFull()
            ->schema([
                TextInput::make('title')->label(__(key: 'panel.title'))
                    ->columnSpanFull()
                    ->required(),
                RichEditor::make('description')->label(__(key: 'panel.text'))
                    ->required()
                    ->columnSpanFull()
                    ->extraFieldWrapperAttributes(['class' => 'mb-4']),
                TextInput::make('btnLabel')->label(__(key: 'panel.btn_label'))
                    ->required()
                    ->live(onBlur: true)
                    ->columnSpanFull(),
                UrlInput::make('btnUrl')->label(__(key: 'panel.btn_url'))
                    ->columnSpanFull()
                    ->required(),
            ]);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.info-block-with-buttons';
    }
}