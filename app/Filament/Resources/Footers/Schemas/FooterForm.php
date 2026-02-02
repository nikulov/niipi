<?php

namespace App\Filament\Resources\Footers\Schemas;

use App\Filament\Forms\Components\CustomRepeater;
use App\Filament\Forms\Components\UrlInput;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class FooterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                RichEditor::make('contact_data')->label(__(key: 'panel.contact-data'))
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
                    ]),
                
                CustomRepeater::make('social_data')->label(__(key: 'panel.social'))
                    ->deleteAction(
                        fn(Action $action) => $action->requiresConfirmation(),
                    )
                    ->maxItems(20)
                    ->itemLabel(fn(array $state): string => $state['title'] ?? __('panel.social_icon'))
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->addActionLabel(__(key: 'panel.add_social_icon'))
                    ->columnSpanFull()
                    ->columns(24)
                    ->collapsed()
                    ->reorderable()
                    ->schema([
                        Group::make([
                            FileUpload::make('iconUrl')->label(__(key: 'panel.icon') . ' (' . __('panel.svg') . ')')
                                ->columnSpan(8)
                                ->preserveFilenames()
                                ->downloadable()
                                ->openable()
                                ->moveFiles()
                                ->disk('public')
                                ->directory('images/footer')
                                ->visibility('public')
                                ->image()
                                ->imageEditor()
                                ->acceptedFileTypes(['image/svg+xml'])
                                ->required(),
                            UrlInput::make('url')->label(__('panel.url'))
                                ->columnSpan(16)
                                ->required()
                                ->prefix(false),
                        ])->columns(24)->columnSpanFull(),
                    ])
            ])->columns(24);
    }
}
