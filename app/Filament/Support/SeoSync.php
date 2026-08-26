<?php

namespace App\Filament\Support;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

final class SeoSync
{
    /** Mirror a content field into its meta twin, unless that meta field was written by hand. */
    public static function copy(Set $set, Get $get, string $metaField, ?string $state, ?string $old): void
    {
        $current = $get($metaField);

        if (filled($current) && $current !== $old) {
            return;
        }

        $set($metaField, (string) $state);
    }
}
