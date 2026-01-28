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
                RichEditor::make('textFull')->label(__('panel.paragraph'))
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                        [
                            'h2',
                            'h3',
                            'highlight',
                            'horizontalRule',
                            'lead',
                            'alignStart',
                            'alignCenter',
                            'alignEnd',
                            'alignJustify',
                            'grid',
                            'gridDelete'
                        ],
                        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                        ['table', 'attachFiles'],
                        ['undo', 'redo'],
                    ])
            ]);
    }
}
