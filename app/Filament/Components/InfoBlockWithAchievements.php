<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\CustomRepeater;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


final class InfoBlockWithAchievements
{
    public static function key(): string
    {
        return 'info-block-with-achievements';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.info_block_with_achievements'))
            ->columnSpanFull()
            ->schema([
                
                Textarea::make('title')->label(__(key: 'panel.title'))
                    ->autosize()
                    ->trim()
                    ->required(),
                
                RichEditor::make('description')->label(__(key: 'panel.text'))
                    ->required()
                    ->columnSpanFull()
                    ->extraFieldWrapperAttributes(['class' => 'mb-4']),
                
                CustomRepeater::make('achievements')->label(__(key: 'panel.achievements'))
                    ->maxItems(6)
                    ->grid(2)
                    ->itemLabel(
                        fn(array $state
                        ): string => $state['quantity'] . ' ' . ($state['title'] ?? __(key: 'panel.achievement'))
                    )
                    ->deleteAction(
                        fn(Action $action) => $action->requiresConfirmation(),
                    )
                    ->cloneable()
                    ->addActionLabel(__(key: 'panel.add_achievement'))
                    ->columnSpanFull()
                    ->collapsed()
                    ->reorderable()
                    ->columns(12)
                    ->schema([
                        TextInput::make('quantity')->label(__(key: 'panel.quantity'))
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('title')->label(__(key: 'panel.title'))
                            ->required()
                            ->live(onBlur: true)
                            ->columnSpan(8),
                        TextInput::make('description')->label(__(key: 'panel.description'))
                            ->required()
                            ->columnSpan(12),
                    ]),
                
                FileUpload::make('imageUrl')->label(__(key: 'panel.add_background_image'))
                    ->columnSpanFull()
                    ->preserveFilenames()
                    ->moveFiles()
                    ->disk('public')
                    ->directory('images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([null, '16:9']),
            
            ]);
    }
}