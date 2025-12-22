<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;

final class InfoBlockWithButtonsRenderer implements BlockRenderer
{
    public static function key(): string { return 'info-block-with-buttons'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        return view('components.sections.info-block-with-button', $data)->render();
    }
    
}