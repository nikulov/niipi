<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
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
            self::Published => (__( 'panel.published' )),
            self::Archived => (__( 'panel.archived' )),
        };
    }
    
    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Published => 'heroicon-o-check-circle',
            self::Archived => 'heroicon-o-x-circle',
        };
        
    }
}
