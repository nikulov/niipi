<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


final class ImageTittleFullWidth
{
    public static function key(): string
    {
        return 'image-tittle-full-width';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.image_tittle_full_width'))
            ->columnSpanFull()
            ->schema([
                FileUpload::make('iconUrl')->label(__(key: 'panel.icon'))
                    ->columnSpan(12)
                    ->preserveFilenames()
                    ->disk('public')
                    ->directory('images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([null, '16:9'])
                    ->required(),
                TextInput::make('iconAlt')->label(__(key: 'panel.icon_alt'))
                    ->required()
                    ->trim()
                    ->columnSpan(12),
                Textarea::make('title')->label(__(key: 'panel.title'))
                    ->columnSpan(24)
                    ->maxLength(255)
                    ->trim()
                    ->required()
                    ->live(onBlur: true),
                FileUpload::make('imageUrl')->label(__(key: 'panel.image'))
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
                TextInput::make('imageAlt')->label(__(key: 'panel.image_alt'))
                    ->required()
                    ->trim()
                    ->columnSpanFull(),
            ])->columns(24);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.image-tittle-full-width';
    }
}
