<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Models\Page;

final class SliderFullWidthRenderer implements BlockRenderer
{
    public static function key(): string { return 'slider-full-width'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, Page $page, int $index): string
    {
        
        $sliders = array_values((array)($data['sliders'] ?? []));
        
        return view('components.sections.slider-full-width',
            [
                'sliders' => $sliders,
            ]
        )->render();
    }
}