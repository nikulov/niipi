<?php


namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

final class CategoryList
{
    public static function key(): string
    {
        return 'category-list';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('page.category-list.label'))
            ->columnSpanFull();
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.category-list';
    }
}
