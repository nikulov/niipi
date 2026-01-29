<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\ProjectStatus;
use App\Filament\Components\BlockRegistry\BlockRegistry;
use App\Filament\Components\CategoryList;
use App\Filament\Components\ImageTittleFullWidth;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                Fieldset::make('settings')->label(__('panel.settings'))
                    ->columns(24)
                    ->columnSpanFull()
                    ->schema([
                        
                        Textarea::make('title')->label(__('panel.title'))
                            ->required()
                            ->trim()
                            ->autosize()
                            ->columnSpan(24)
                            ->maxLength(500)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation) {
                                // Only auto-generate slug on create and only if slug is empty
                                if ($operation !== 'create' || filled($get('slug'))) {
                                    return;
                                }
                                
                                $set('slug', Str::slug((string) $state));
                            }),
                        
                        Textarea::make('description')->label(__('panel.excerpt_project'))
                            ->required()
                            ->trim()
                            ->autosize()
                            ->columnSpan(24)
                            ->maxLength(1000),
                        
                        TextInput::make('slug')->label(__('panel.slug'))
                            ->maxLength(255)
                            ->columnSpan(24)
                            ->trim()
                            ->required()
                            ->prefix('niipigrad.ru/projects/')
                            ->suffixActions([
                                Action::make('open')
                                    ->icon('heroicon-o-globe-alt')
                                    ->color('success')
                                    ->hiddenLabel()
                                    ->url(fn ($state) => $state ? url('projects/' . ltrim($state, '/')) : null)
                                    ->openUrlInNewTab()
                                    ->tooltip(__('panel.open_page_in_new_tab'))
                                    ->extraAttributes(['class' => 'text-green-500 [&>svg]:text-green-500']),
                            ])
                            ->unique(Project::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        
                        Group::make()->schema([
                            
                            Select::make('status')->label(__('panel.status'))
                                ->columnSpan(6)
                                ->required()
                                ->options(ProjectStatus::class)
                                ->default(ProjectStatus::Draft),
                            
                            Select::make('category_id')->label(__('panel.category'))
                                ->multiple()
                                ->preload()
                                ->relationship(
                                    'categories',
                                    'name',
                                    modifyQueryUsing: fn (EloquentBuilder $query) => $query->projects())
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
                            ->default('gallery/projects/default/temp-image-dark2-1766411627.jpg')
                            ->disk('public')
                            ->directory('gallery/projects/default')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(2048), // 2MB
                    ]),
                
                Fieldset::make('seo')->label(__('panel.seo'))
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        
                        //todo removed and unit to json for all meta
                        TextInput::make('meta_title')->label(__('panel.meta_title'))
                            ->columnSpan(6)
                            ->trim()
                            ->maxLength(500),
                        
                        Textarea::make('meta_description')->label(__('panel.meta_description'))
                            ->columnSpan(12)
                            ->autosize()
                            ->trim(),
                    ]),
                
                Fieldset::make('top_items')->label(__('panel.top_section'))
                    ->columnSpanFull()
                    ->schema([
                        
                        Builder::make('top_section')->label('')
                            ->addActionLabel(__(key: 'panel.add_top_block'))
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->hiddenLabel()
                            ->collapsible()
                            ->collapsed()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->blockPickerWidth('md')
                            ->default(Project::getDefaultBlock())
                            ->blocks(BlockRegistry::topSection())
                    ]),
                
                Fieldset::make('main_items')->label(__('panel.main_section'))
                    ->columnSpanFull()
                    ->schema([
                        
                        Builder::make('main_section')->label('')
                            ->addActionLabel(__(key: 'panel.add_main_block'))
                            ->collapsible()
                            ->collapsed()
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->hiddenLabel()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->blockPickerWidth('md')
                            ->blocks(BlockRegistry::mainSection())
                    ]),
                
                Fieldset::make('bottom_items')->label(__('panel.bottom_section'))
                    ->columnSpanFull()
                    ->schema([
                        
                        Builder::make('bottom_section')->label('')
                            ->addActionLabel(__(key: 'panel.add_bottom_block'))
                            ->deleteAction(
                                fn(Action $action) => $action->requiresConfirmation(),
                            )
                            ->hiddenLabel()
                            ->collapsible()
                            ->collapsed()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->blockPickerWidth('md')
                            ->blocks(BlockRegistry::bottomSection())
                    ])
            
            ]);
    }
}
