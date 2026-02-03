<?php


namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;

final class YandexMap
{
    public static function key(): string
    {
        return 'yandex-map';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.yandex-map'))
            ->columnSpanFull();
    }
}
