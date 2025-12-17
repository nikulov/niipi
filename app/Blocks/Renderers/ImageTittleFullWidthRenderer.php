<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Models\Page;

final class ImageTittleFullWidthRenderer implements BlockRenderer
{
    public static function key(): string { return 'image-tittle-full-width'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, Page $page, int $index): string
    {
        return view('components.sections.image-title-full-width', $data)->render();
    }
}