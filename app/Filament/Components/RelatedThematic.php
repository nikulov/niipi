<?php

namespace App\Filament\Components;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

final class RelatedThematic
{
    public static function key(): string
    {
        return 'related-thematic';
    }

    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.related_thematic_label'))
            ->columnSpanFull()
            ->columns(12)
            ->schema([

                Textarea::make('title')->label(__('panel.title'))
                    ->autosize()
                    ->columnSpan(4)
                    ->default(__('panel.related_thematic'))
                    ->required(),

                TextInput::make('limit')->label(__('panel.limit'))
                    ->columnSpan(2)
                    ->numeric()
                    ->default(5)
                    ->required(),

                TextInput::make('btnLabel')->label(__('panel.btn_label'))
                    ->columnSpan(6)
                    ->default(__('panel.related_thematic_all_btn'))
                    ->required(),

                Select::make('categoryIds')->label(__('panel.category'))
                    ->multiple()
                    ->preload()
                    ->options(fn ($livewire) => self::categoryOptions($livewire))
                    ->columnSpan(12)
                    ->helperText(__('panel.related_thematic_categories_hint')),
            ]);
    }

    public static function getDefaultBlock(): array
    {
        return [
            [
                'type' => self::key(),
                'data' => [
                    'title' => __('panel.related_thematic'),
                    'limit' => 5,
                    'btnLabel' => __('panel.related_thematic_all_btn'),
                ],
            ],
        ];
    }

    private static function categoryOptions($livewire): array
    {
        $type = self::resolveCategoryType($livewire);

        if ($type === null) {
            return [];
        }

        return Category::query()
            ->where('type', $type->value)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    private static function resolveCategoryType($livewire): ?CategoryType
    {
        $modelClass = null;

        if (is_object($livewire) && method_exists($livewire, 'getRecord')) {
            $record = $livewire->getRecord();
            if ($record) {
                $modelClass = $record::class;
            }
        }

        if ($modelClass === null && is_object($livewire) && method_exists($livewire, 'getResource')) {
            $resource = $livewire::getResource();
            if (is_string($resource) && method_exists($resource, 'getModel')) {
                $modelClass = $resource::getModel();
            }
        }

        return match ($modelClass) {
            Post::class => CategoryType::Posts,
            Project::class => CategoryType::Projects,
            default => null,
        };
    }
}
