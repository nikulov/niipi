<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FormApplicantType: string implements HasColor, HasLabel
{
    case Person = 'person';
    case Company = 'company';
    case All = 'all';
    
    public function getColor(): ?string
    {
        return match ($this) {
            self::Person => 'default',
            self::Company => 'default',
            self::All => 'default',
        };
    }
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Person => (__('panel.person')),
            self::Company => (__('panel.company')),
            self::All => (__('panel.all')),
        };
    }
    
    
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
    
}
