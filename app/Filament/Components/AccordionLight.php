<?php


namespace App\Filament\Components;

use App\Filament\Forms\Components\CustomRepeater;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

final class AccordionLight
{
    public static function key(): string
    {
        return 'accordion-light';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('page.accordion-light'))
            ->columnSpanFull()
            ->schema([
                TextInput::make('title')->label(__(key: 'panel.title'))
                    ->maxLength(500),
                CustomRepeater::make('accordions')->label('')
                    ->deleteAction(
                        fn(Action $action) => $action->requiresConfirmation(),
                    )
                    ->label('')
                    ->maxItems(20)
                    ->hiddenLabel()
                    ->itemLabel(fn(array $state): string => $state['title'] ?? __('page.accordion'))
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->addActionLabel(__(key: 'panel.add_accordion'))
                    ->columnSpanFull()
                    ->columns(12)
                    ->collapsed()
                    ->reorderable()
                    ->schema([
                        Textarea::make('itemTitle2')->label(__('page.title'))
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->required()
                            ->live(onBlur: true),
                        RichEditor::make('item')->label(__('panel.content'))
                            ->columnSpanFull()
                            ->required()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                                [
                                    'h2',
                                    'h3',
                                    'textColor',
                                    'highlight',
                                    'horizontalRule',
                                    'alignStart',
                                    'alignCenter',
                                    'alignEnd',
                                    'grid'
                                ],
                                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                ['table', 'attachFiles'],
                                ['undo', 'redo'],
                            ])
                    ]),
            
            ]);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.accordion-light';
    }
}