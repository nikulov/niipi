<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;

final class ImageText
{
    public static function key(): string
    {
        return 'image-text';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.image_text'))
            ->columnSpanFull()
            ->schema([
                
                Textarea::make('title')->label(__('panel.heading'))
                    ->autosize()
                    ->columnSpanFull()
                    ->trim(),
            
                Textarea::make('subtitle')->label(__('panel.subheading'))
                    ->autosize()
                    ->columnSpanFull()
                    ->trim(),
                
                FileUpload::make('url')->label(__(key: 'panel.image'))
                    ->columnSpan(12)
                    ->preserveFilenames()
                    ->moveFiles()
                    ->disk('public')
                    ->directory('images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([null, '16:9'])
                    ->required(),
                Group::make ([
                    TextInput::make('alt')->label(__(key: 'panel.image_alt'))
                        ->required()
                        ->trim()
                        ->columnSpan(6),
                    Select::make('position')->label(__('panel.position_image'))
                        ->default(1)
                        ->options([
                            1 => __('panel.left'),
                            2 => __('panel.right'),
                        ])
                        ->required(),
                ])->columnSpan(12),
                
                RichEditor::make('content')->label(__(key: 'panel.content'))
                    ->resizableImages()
                    ->required()
                    ->columnSpan(24)
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                        [
                            'h2',
                            'h3',
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
            ])->columns(24);
    }
}
