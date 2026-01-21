<?php


namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

final class Title
{
    public static function key(): string
    {
        return 'title';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.title_label'))
            ->columnSpanFull()
            ->schema([
                Textarea::make('title')->label(__('panel.heading'))
                    ->rows(2)
                    ->columnSpanFull()
                    ->trim()
                    ->required(),
                Select::make('type')->label(__('panel.heading_size'))
                    ->options([
                        'h2' => __('panel.heading') . ' 2',
                        'h3' => __('panel.heading') . ' 3',
                    ])
                    ->required()
                    ->columnSpan(6),
                Select::make('position')->label(__('panel.position_title'))
                    ->default('left')
                    ->options([
                        'left' => __('panel.left'),
                        'center' => __('panel.center'),
                        'right' => __('panel.right'),
                    ])
                    ->required()
                    ->columnSpan(6),
            ])->columns(12);
    }
}
