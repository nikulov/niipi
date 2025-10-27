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
                Fieldset::make('settings')->label(__('panel.settings'))
                    ->columns(24)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')->label(__('panel.title'))
                            ->required()
                            ->columnSpan(24)
                            ->maxLength(500)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn(Set $set, ?string $state) => $set('slug', Str::slug($state))
                            ),
                        TextInput::make('slug')->label(__('panel.slug'))
                            ->maxLength(255)
                            ->columnSpan(24)
                            ->prefix('niipigrad.ru/')
                            ->suffixActions([
                                Action::make('open')
                                    ->icon('heroicon-o-globe-alt')
                                    ->color('success')
                                    ->hiddenLabel()
                                    ->url(fn($state) => $state ? url($state) : null)
                                    ->openUrlInNewTab()
                                    ->tooltip(__('panel.open_page_in_new_tab'))
                                    ->extraAttributes(['class' => 'text-green-500 [&>svg]:text-green-500']),
                            ])
                            ->disabled()
                            ->dehydrated()
                            ->unique(Post::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Group::make()->schema([
                            Select::make('status')->label(__('panel.status'))
                                ->columnSpan(6)
                                ->required()
                                ->options(PostStatus::class)
                                ->default(PostStatus::Draft),
                            Select::make('category_id')->label(__('panel.category'))
                                ->multiple()
                                ->preload()
                                ->relationship('category', 'name')
                                ->columnSpan(6),
                        ])->columnSpan(8),
                        FileUpload::make('thumbnail')->label(__('panel.thumbnail'))
                            ->columnSpan(16)
                            ->getUploadedFileNameForStorageUsing(
                                fn($file) => str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                    ->slug()
                                    ->limit(20)
                                    ->append('-' . time() . '.' . $file->getClientOriginalExtension())
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
                            ->maxSize(1024), // 1MB
                    ]),
                Fieldset::make('seo')->label(__('panel.seo'))
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('meta_title')->label(__('panel.meta_title'))
                            ->columnSpan(6)
                            ->maxLength(500),
                        Textarea::make('meta_keywords')->label(__('panel.meta_keywords'))
                            ->columnSpan(6)
                            ->maxLength(2000),
                        Textarea::make('meta_description')->label(__('panel.meta_description'))
                            ->columnSpan(12)
                    ]),
                Builder::make('content')->label(__('panel.content'))
                    ->deleteAction(
                        fn(Action $action) => $action->requiresConfirmation()
                    )
                    ->columnSpanFull()
                    ->reorderableWithButtons()
                    ->addActionLabel(__('panel.add_block'))
                    ->blocks([
                        Block::make('heading')->label(__('panel.heading'))
                            ->schema([
                                TextInput::make('content')->label(__('panel.heading'))
                                    ->required(),
                                Select::make('heading_size')->label(__('panel.heading_size'))
                                    ->options([
                                        'h2' => __('panel.heading') . ' 2',
                                        'h3' => __('panel.heading') . ' 3',
                                        'h4' => __('panel.heading') . ' 4',
                                        'h5' => __('panel.heading') . ' 5',
                                        'h6' => __('panel.heading') . ' 6',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2),
                        Block::make('paragraph')->label(__('panel.paragraph'))
                            ->schema([
                                RichEditor::make('content')
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
                            ]),
                        Block::make('image')->label(__('panel.image'))
                            ->columns(2)
                            ->schema([
                                FileUpload::make('url')
                                    ->columnSpan(1)
                                    ->label(__('panel.choose_image'))
                                    ->getUploadedFileNameForStorageUsing(
                                        fn($file) => str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                            ->slug()
                                            ->limit(20)
                                            ->append('-' . time() . '.' . $file->getClientOriginalExtension())
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
                                    TextInput::make('alt')->label(__('panel.alt_text'))
                                        ->required(),
                                    Select::make('alignment')->label(__('panel.alignment'))
                                        ->options([
                                            'left' => __('panel.left'),
                                            'center' => __('panel.center'),
                                            'right' => __('panel.right'),
                                        ])
                                ])->columnSpan(1),
                            
                            ]),
                        Block::make('galery')->label(__('panel.gallery'))
                            ->schema([
                                FileUpload::make('url')->label(__('panel.choose_images'))
                                    ->required()
                                    ->multiple()
                                    ->getUploadedFileNameForStorageUsing(
                                        fn($file) => str(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                            ->slug()
                                            ->limit(20)
                                            ->append('-' . time() . '.' . $file->getClientOriginalExtension())
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
