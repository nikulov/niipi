<?php


namespace App\Filament\Components;

use App\Models\Category;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;


final class ProjectsFull
{
    public static function key(): string
    {
        return 'projects-full';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.projects_full'))
            ->columnSpanFull()
            ->columns(12)
            ->schema([
                TextInput::make('limit')->label(__(key: 'panel.limit_per_page'))
                    ->columnSpan(2)
                    ->numeric()
                    ->default(10)
                    ->required(),
                Select::make('categoryIds')->label(__('panel.category'))
                    ->multiple()
                    ->preload()
                    ->default(Category::query()
                        ->where('type', 'projects')
                        ->pluck('id', 'name')
                        ->all())
                    ->options(Category::query()
                        ->where('type', 'projects')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->columnSpan(10),
            ]);
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.projects-full';
    }
}