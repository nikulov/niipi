<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Models\Page;

final class InfoBlockWithAchievementsRenderer implements BlockRenderer
{
    public static function key(): string { return 'info-block-with-achievements'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, Page $page, int $index): string
    {
        return view('components.sections.info-block-with-achievements', $data)->render();
    }
}