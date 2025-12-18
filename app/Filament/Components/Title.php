<?php


namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


final class Title
{
    public static function key(): string
    {
        return 'title';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('page.title.label'))
            ->columnSpanFull()
            ->schema([
                Textarea::make('title')->label(__('panel.heading'))
                    ->rows(2)
                    ->trim()
                    ->required(),
                Select::make('type')->label(__('panel.heading_size'))
                    ->options([
                        'h2' => __('panel.heading') . ' 2',
                        'h3' => __('panel.heading') . ' 3',
                        'h4' => __('panel.heading') . ' 4',
                        'h5' => __('panel.heading') . ' 5',
                        'h6' => __('panel.heading') . ' 6',
                    ])
                    ->required(),
            ]);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.title';
    }
}
