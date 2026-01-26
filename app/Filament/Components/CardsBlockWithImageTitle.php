<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\CustomRepeater;
use App\Filament\Forms\Components\UrlInput;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


final class CardsBlockWithImageTitle
{
    public static function key(): string
    {
        return 'cards-block-with-image-title';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.cards_block_with_image_title'))
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
                        
                        FileUpload::make('cardFileUrl')->label(__(key: 'panel.file'))
                            ->columnSpanFull()
                            ->preserveFilenames()
                            ->moveFiles()
                            ->maxFiles(1)
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('files')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([null, '16:9'])
                            ->acceptedFileTypes([
                                'image/svg+xml',
                                'image/png',
                                'image/jpeg',
                                'image/webp',
                                'application/pdf',
                            ])
                            ->required(),
                        
                        TextInput::make('imageAlt')->label(__(key: 'panel.image_alt'))
                            ->trim()
                            ->columnSpanFull(),
                        Textarea::make('cardTitle')->label(__(key: 'panel.title'))
                            ->live(onBlur: true)
                            ->trim(),
                        
                        Textarea::make('cardDescription')->label(__(key: 'panel.description'))
                            ->trim(),
                        
                        UrlInput::make('cardUrl')->label(__(key: 'panel.btn_url'))
                            ->placeholder(__(key: 'panel.url_placeholder')),
                        
                    ]),
            ])->columns(24);
    }
}