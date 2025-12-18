<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Models\Page;

final class ImageTextRenderer implements BlockRenderer
{
    public static function key(): string { return 'image-text'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, Page $page, int $index): string
    {
        return view('components.sections.image-text', $data)->render();
    }
    
}