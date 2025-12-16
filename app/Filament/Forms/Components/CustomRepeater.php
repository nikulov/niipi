<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Illuminate\Contracts\Support\Htmlable;

class CustomRepeater extends Repeater
{
    public function getItemLabel(string $key): Htmlable|string|null
    {
        $baseLabel = parent::getItemLabel($key);
        $all = (array)$this->getState();
        $keys = array_keys($all);
        $index = array_search($key, $keys, true);
        $n = ($index === false ? 0 : $index) + 1;
        
        if ($baseLabel) {
            return "{$n} - {$baseLabel}";
        }
        
        return "";
    }
}