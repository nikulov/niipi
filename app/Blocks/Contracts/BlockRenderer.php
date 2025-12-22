<?php

namespace App\Blocks\Contracts;

use App\Models\Page;

interface BlockRenderer
{
    /** Unique key equal to Builder "type" */
    public static function key(): string;
    
    /** Bump when renderer logic changes */
    public static function version(): string;
    
    /** Return rendered HTML of the block */
    public function render(array $data, HasBlockSections $model, int $index): string;
}