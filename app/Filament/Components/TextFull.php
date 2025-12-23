<?php


namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;


final class TextFull
{
    public static function key(): string
    {
        return 'text-full';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.text_full_label'))
            ->columnSpanFull()
            ->schema([
                RichEditor::make('textFull')
                    ->label(__('panel.paragraph'))
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
            ]);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.text-full';
    }
}
