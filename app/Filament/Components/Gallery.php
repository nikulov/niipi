<?php


namespace App\Filament\Components;

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
                    ->getUploadedFileNameForStorageUsing(
                        fn($file) => str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                            ->slug()
                            ->limit(25)
                            ->append('-' . time() . '.' . $file->getClientOriginalExtension())
                            ->toString()
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
                    ->maxSize(2048), // 2MB
            ]);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.gallery';
    }
}
