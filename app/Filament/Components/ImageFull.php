<?php

namespace App\Filament\Components;

use App\Filament\Forms\Components\MediaPickerAction;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

final class ImageFull
{
    public static function key(): string
    {
        return 'image-full';
    }

    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.image_full'))
            ->columnSpanFull()
            ->schema([

                FileUpload::make('url')->label(__(key: 'panel.image'))
                    ->columnSpanFull()
                    ->downloadable()
                    ->openable()
                    ->preserveFilenames()
                    ->moveFiles()
                    ->disk('public')
                    ->directory('images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([null, '16:9'])
                    ->required()
                    ->hintAction(MediaPickerAction::make('url', imagesOnly: true)),

                TextInput::make('alt')->label(__(key: 'panel.image_alt'))
                    ->required()
                    ->trim()
                    ->columnSpanFull(),

            ])->columns(24);
    }
}
