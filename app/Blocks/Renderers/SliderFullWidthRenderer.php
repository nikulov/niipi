<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;

final class SliderFullWidthRenderer implements BlockRenderer
{
    public static function key(): string { return 'slider-full-width'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        
        $sliders = array_values((array)($data['sliders'] ?? []));
        
        return view('components.sections.slider-full-width',
            [
                'sliders' => $sliders,
            ]
        )->render();
    }
}