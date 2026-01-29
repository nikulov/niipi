<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;


final class BgForMainSection
{
    public static function key(): string
    {
        return 'bg-for-main-section';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.bg_for_main_section'))
            ->columnSpanFull()
            ->schema([
                
                FileUpload::make('bgForMainSection')->label(__(key: 'panel.image'))
                    ->columnSpanFull()
                    ->preserveFilenames()
                    ->moveFiles()
                    ->disk('public')
                    ->directory('images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([null, '16:9'])
                    ->required(),
                
            ])->columns(24);
    }
}
