<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PostStatus: string implements HasColor, HasLabel
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
            self::Draft => (__('Draft')),
            self::Published => (__( 'Published' )),
            self::Archived => (__( 'Archived' )),
        };
    }
}
