<?php


namespace App\Filament\Components;

use App\Filament\Components\BlockRegistry\BlockRegistry;
use App\Filament\Forms\Components\CustomRepeater;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;


final class TabsBlock
{
    public static function key(): string
    {
        return 'tabs-block';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__(key: 'panel.tabs_block'))
            ->schema([
                
                CustomRepeater::make('tabs')->label(__(key: 'panel.tabs'))
                    ->minItems(2)
                    ->defaultItems(2)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): string => $state['title'] ?? __('panel.tab'))
                    ->schema([
                        
                        TextInput::make('title')->label(__(key: 'panel.title'))
                            ->required()
                            ->trim()
                            ->maxLength(120),
                        
                        Builder::make('tab')->label('')
                            ->addActionLabel(__(key: 'panel.add_block'))
                            ->collapsible()
                            ->collapsed()
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->hiddenLabel()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->blockPickerWidth('md')
                            ->blocks(BlockRegistry::tabs())
                    ]),
            ]);
    }
}
