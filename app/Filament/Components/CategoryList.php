<?php


namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;

final class CategoryList
{
    public static function key(): string
    {
        return 'category-list';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.category_list_label'))
            ->columnSpanFull();
    }
    
    /** Blade view for frontend */
    public static function view(): string
    {
        return 'components.sections.category-list';
    }
    
    public static function getDefaultBlock(): array
    {
        return
            [
                [
                    'type' => self::key(),
                    'data' => [],
                ]
            ];
    }
}
