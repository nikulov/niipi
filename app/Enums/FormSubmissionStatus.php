<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FormSubmissionStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case Processing = 'processing';
    case Sent = 'sent';
    case Failed = 'failed';
    
    public function getColor(): ?string
    {
        return match ($this) {
            self::New => 'gray',
            self::Processing => 'warning',
            self::Sent => 'success',
            self::Failed => 'danger',
        };
    }
    public function getLabel(): ?string
    {
        return match ($this) {
            self::New => __('panel.status_new'),
            self::Processing => __('panel.status_processing'),
            self::Sent => __('panel.status_handled'),
            self::Failed => __('panel.status_failed'),
        };
    }
    
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->getLabel(),
            ])
            ->toArray();
    }
}
