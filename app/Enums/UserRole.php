<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';
    
    public function getColor(): ?string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Editor => 'success',
            self::Viewer => 'grey',
        };
    }
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Admin => (__('panel.admin')),
            self::Editor => (__( 'panel.editor' )),
            self::Viewer => (__( 'panel.viewer' )),
        };
    }
    
    public function getIcon(): ?string
    {
        return match ($this) {
            self::Admin => 'heroicon-o-pencil-square',
            self::Editor => 'heroicon-o-check-circle',
            self::Viewer => 'heroicon-o-x-circle',
        };
        
    }
    
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
