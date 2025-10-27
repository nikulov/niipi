<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CategoryStatus;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('general')->label(__('General'))
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')->label(__('Name'))
                            ->columnSpan(4)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->columnSpan(4)
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
                        Select::make('status')->label(__('Status'))
                            ->columnSpan(4)
                            ->options(CategoryStatus::class)
                            ->default(CategoryStatus::Draft),
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
                        ->columnSpan(12),
                    ])
            ]);
    }
}
