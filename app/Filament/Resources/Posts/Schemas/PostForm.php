<?php

namespace App\Filament\Resources\Posts\Schemas;


use App\Enums\PostStatus;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Settings')->label(__('Settings'))
                    ->columns(24)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpan(9)
                            ->maxLength(500)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->maxLength(255)
                            ->columnSpan(9)
                            ->prefix('niipigrad.ru/')
                            ->label('Slug')
                            ->suffixActions([
                                Action::make('open')
                                    ->icon('heroicon-o-globe-alt')
                                    ->color('success')
                                    ->hiddenLabel()
                                    ->url(fn($state) => $state ? url($state) : null)
                                    ->openUrlInNewTab()
                                    ->tooltip(__('Open page in new tab'))
                                    ->extraAttributes(['class' => 'text-green-500 [&>svg]:text-green-500']),
                            ])
                            ->disabled()
                            ->dehydrated()
                            ->unique(Post::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('status')
                            ->columnSpan(6)
                            ->required()
                            ->options(PostStatus::class)
                            ->default(PostStatus::Draft),
                        FileUpload::make('thumbnail')
                            ->columnSpan(12)
                            ->getUploadedFileNameForStorageUsing(
                                fn($file) => str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                    ->slug()
                                    ->limit(20)
                                    ->append('-' . time() . '.'.$file->getClientOriginalExtension())
                                    ->toString()
                            )
                            ->moveFiles()
                            ->disk('public')
//                            ->directory(
//                                fn(callable $get) => $get('slug')
//                                    ? "gallery/posts/{$get('slug')}" : 'gallery/posts/default'
//                            )
//                            ->directory(fn($record) =>
//                            $record?->id
//                                ? "gallery/posts/{$record->id}"
//                                : "gallery/posts/__default"
//                            )
                            ->directory('gallery/posts/default')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(1024) // 1MB
                    
                    ]),
                Fieldset::make('seo')->label(__('SEO'))
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('meta_title')
                            ->columnSpan(6)
                            ->maxLength(500),
                        Textarea::make('meta_keywords')
                            ->columnSpan(6)
                            ->maxLength(2000),
                        Textarea::make('meta_description')
                            ->columnSpan(12)
                    ]),
                Builder::make('content')->label(__('Content'))
                    ->deleteAction(fn(Action $action) => $action->requiresConfirmation())
                    ->columnSpanFull()
                    ->reorderableWithButtons()
                    ->addActionLabel(__('Add block'))
                    ->blocks([
                        Block::make('heading')->label(__('Heading'))
                            ->schema([
                                TextInput::make('content')
                                    ->label(__('Heading'))
                                    ->required(),
                                Select::make('heading_size')
                                    ->options([
                                        'h2' => 'Heading 2',
                                        'h3' => 'Heading 3',
                                        'h4' => 'Heading 4',
                                        'h5' => 'Heading 5',
                                        'h6' => 'Heading 6',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2),
                        Block::make('paragraph')->label(__('Paragraph'))
                            ->schema([
                                RichEditor::make('content')
                                    ->label(__('Paragraph'))
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
                        Block::make('image')->label(__('Image'))
                            ->columns(2)
                            ->schema([
                                FileUpload::make('url')
                                    ->columnSpan(1)
                                    ->label(__('Choose image'))
                                    ->getUploadedFileNameForStorageUsing(
                                        fn($file) => str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                            ->slug()
                                            ->limit(20)
                                            ->append('-' . time() . '.'.$file->getClientOriginalExtension())
                                            ->toString()
                                    )
                                    ->moveFiles()
                                    ->disk('public')
                                    ->directory('gallery/posts/default')
                                    ->visibility('public')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        null,
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->required(),
                                Group::make()->schema([
                                    TextInput::make('alt')
                                        ->label(__('Alt text'))
                                        ->required(),
                                    Select::make('alignment')->label(__('Alignment'))
                                        ->options([
                                            'left' => 'Left',
                                            'center' => 'Center',
                                            'right' => 'Right',
                                        ])
                                ])->columnSpan(1),
                            
                            ]),
                        Block::make('galery')->label(__('Galery'))
                            ->schema([
                                FileUpload::make('url')->label(__('Choose images'))
                                    ->required()
                                    ->multiple()
                                    ->getUploadedFileNameForStorageUsing(
                                        fn($file) => str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                            ->slug()
                                            ->limit(20)
                                            ->append('-' . time() . '.'.$file->getClientOriginalExtension())
                                            ->toString()
                                    )
                                    ->moveFiles()
                                    ->disk('public')
                                    ->directory('gallery/posts/default')
                                    ->visibility('public')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        null,
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->minFiles(2)
                                    ->maxFiles(20)
                                    ->maxSize(2048) // 2MB
                            
                            ])
                    ]),
            ]);
    }
}
