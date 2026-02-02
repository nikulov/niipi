<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\CustomRepeater;
use App\Filament\Forms\Components\UrlInput;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

final class SliderFullWidth
{
    public static function key(): string
    {
        return 'slider-full-width';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.slider_full_width'))
            ->columnSpanFull()
            ->schema([
                
                CustomRepeater::make('sliders')->label('')
                    ->deleteAction(
                        fn(Action $action) => $action->requiresConfirmation(),
                    )
                    ->label('')
                    ->maxItems(20)
                    ->minItems(2)
                    ->hiddenLabel()
                    ->itemLabel(fn(array $state): string => $state['title'] ?? __('panel.slide'))
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->addActionLabel(__(key: 'panel.add_slide'))
                    ->columnSpanFull()
                    ->collapsed()
                    ->reorderable()
                    ->schema([
                        
                        Textarea::make('title')->label(__(key: 'panel.title'))
                            ->required()
                            ->autosize()
                            ->columnSpanFull()
                            ->trim()
                            ->live(onBlur: true),
                        
                        UrlInput::make('url')->label(__(key: 'panel.title_url'))
                        ->columnSpanFull(),
                        
                        FileUpload::make('iconUrl')->label(__(key: 'panel.icon'))
                            ->columnSpan(12)
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
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
                        
                        FileUpload::make('bgImageUrl')->label(__(key: 'panel.image'))
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->disk('public')
                            ->directory('images')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([null, '16:9'])
                            ->required()
                            ->columnSpan(12),
                        
                        TextInput::make('imageAlt')->label(__(key: 'panel.alt'))
                            ->required()
                            ->trim()
                            ->columnSpan(12),
                        
                    ])->columns(24),
            
            ]);
    }
}