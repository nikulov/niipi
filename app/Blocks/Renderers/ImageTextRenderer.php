<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;

final class ImageTextRenderer implements BlockRenderer
{
    public static function key(): string { return 'image-text'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        return view('components.sections.image-text', $data)->render();
    }
    
}