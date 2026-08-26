<?php

namespace App\Filament\Components;

use App\Filament\Forms\Components\MediaPickerAction;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;

final class Gallery
{
    public static function key(): string
    {
        return 'gallery';
    }

    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.gallery_label'))
            ->columnSpanFull()
            ->schema([

                FileUpload::make('urls')->label(__('panel.choose_images'))
                    ->required()
                    ->multiple()
                    ->downloadable()
                    ->openable()
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file): string => generate_uploaded_file_name($file)
                    )
                    ->moveFiles()
                    ->disk('public')
                    ->directory('images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        null,
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->panelLayout('grid')
                    ->reorderable()
                    ->minFiles(2)
                    ->maxFiles(20)
                    ->maxSize(2048) // 2MB
                    ->hintAction(MediaPickerAction::make('urls', imagesOnly: true, multiple: true, maxSize: 2048)),
            ]);
    }
}
