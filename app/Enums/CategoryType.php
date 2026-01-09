<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CategoryType: string implements HasLabel, HasIcon
{
    case Posts = 'posts';
    case Projects = 'projects';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Posts => __('panel.posts'),
            self::Projects => __('panel.projects'),
        };
    }
    
    public function getIcon(): ?string
    {
        return match ($this) {
            self::Posts => 'heroicon-o-collection',
            self::Projects => 'heroicon-o-briefcase',
        };
    }
    
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
