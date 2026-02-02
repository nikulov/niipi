<?php


namespace App\Filament\Components;

use App\Filament\Components\BlockRegistry\BlockRegistry;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

final class ModalBlock
{
    public static function key(): string
    {
        return 'modal-block';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__(key: 'panel.modal_block'))
            ->schema([
                
                Textarea::make('title')->label(__(key: 'panel.title'))
                    ->autosize()
                    ->trim()
                    ->columnSpan(10),
                
                TextInput::make('modalId')->label(__(key: 'panel.modal_id'))
                    ->columnSpan(8)
                    ->required()
                    ->trim()
                    ->regex('/^[a-z][a-z0-9_-]*$/')
                    ->helperText(__('panel.id_modal_help')),
                
                Toggle::make('widthFull')->label(__(key: 'panel.width_full'))
                    ->default(false)
                    ->inline(false)
                    ->columnSpan(4),
                
                Builder::make('modal')->label('')
                    ->hiddenLabel()
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
                    ->blocks(BlockRegistry::modal())
            
            ])->columns(24);
    }
}
