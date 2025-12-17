<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PageStatus: string implements HasColor, HasLabel
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
            self::Draft => ('Черновик'),
            self::Published => ('Опубликовано'),
            self::Archived => ('Архив'),
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
