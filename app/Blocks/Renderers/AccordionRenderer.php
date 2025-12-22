<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;

final class AccordionRenderer implements BlockRenderer
{
    public static function key(): string { return 'accordion'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        
        $accordions = [
            'title' => trim((string)($data['title'] ?? '')),
            'type'  => $data['type'] ?? 'white',
            'accordions' => array_values((array)($data['accordions'] ?? [])),
        ];
        
        
        return view('components.sections.accordion',
            [
                'accordions' => $accordions,
            ]
        )->render();
    }
}