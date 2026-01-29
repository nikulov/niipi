<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                Fieldset::make('general')->label(__('panel.general'))
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        
                        TextInput::make('name')->label(__('panel.name'))
                            ->columnSpan(6)
                            ->required()
                            ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        
                        TextInput::make('slug')
                            ->columnSpan(6)
                            ->maxLength(255)
                            ->prefix('niipigrad.ru/')
                            ->label('Slug')
                            ->suffixActions([
                                Action::make('open')
                                    ->icon('heroicon-o-globe-alt')
                                    ->hiddenLabel()
                                    ->color('success')
                                    ->url(fn($state) => $state ? url($state) : null)
                                    ->openUrlInNewTab()
                                    ->tooltip(__('Open page in new tab'))
                                    ->extraAttributes(['class' => 'text-green-500 [&>svg]:text-green-500']),
                            ])
                            ->disabled()
                            ->dehydrated()
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        
                        Select::make('type')->label(__('panel.type'))
                            ->required()
                            ->columnSpan(6)
                            ->options(CategoryType::class),
                        
                        Select::make('status')->label(__('panel.status'))
                            ->required()
                            ->columnSpan(6)
                            ->options(CategoryStatus::class),
                    ]),
                
                Fieldset::make('seo')->label(__('SEO'))
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        
                        TextInput::make('meta_title')->label(__('panel.meta_title'))
                            ->columnSpan(6)
                            ->maxLength(500),
                        
                        Textarea::make('meta_description')->label(__('panel.meta_description'))
                            ->columnSpan(12)
                            ->autosize(),
                    ])
            ]);
    }
}
