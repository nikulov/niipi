<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Models\Page;

final class AccordionLightRenderer implements BlockRenderer
{
    public static function key(): string { return 'accordion-light'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, Page $page, int $index): string
    {
        
        $accordions = [
            'title' => trim((string)($data['title'] ?? '')),
            'type'  => $data['type'] ?? 'dark',
            'accordions' => array_values((array)($data['accordions'] ?? [])),
        ];
        
        return view('components.pages.accordion-light',
            [
                'accordions' => $accordions,
            ]
        )->render();
    }
}