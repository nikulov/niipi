<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;

final class YandexMapRenderer implements BlockRenderer
{
    public static function key(): string { return 'yandex-map'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        return view('components.sections.yandex-map', $data)->render();
    }
    
}