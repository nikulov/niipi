<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CategoryStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Published = 'active';
    case Archived = 'archived';
    
    public function getColor(): ?string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Archived => 'danger',
        };
    }
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => (__('panel.draft')),
            self::Published => (__( 'panel.active' )),
            self::Archived => (__( 'panel.archived' )),
        };
    }
}
