<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Services\ContentRenderer;

final class TabsBlockRenderer implements BlockRenderer
{
    public static function key(): string { return 'tabs-block'; }
    public static function version(): string { return '2'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $tabs = $this->normalizeTabs($data['tabs'] ?? null);
        
        $tabsHtml = [];
        
        foreach ($tabs as $i => $tab) {
            $blocks = is_array($tab['tab'] ?? null) ? $tab['tab'] : [];
            
            $tabsHtml[$i] = (string) app(ContentRenderer::class)->renderBlocks(
                blocks: $blocks,
                model: $model,
                section: null,
                indexOffset: ($index * 1000) + ($i * 100)
            );
        }
        
        $defaultIndexSafe = $this->resolveDefaultIndex(
            defaultIndex: $data['defaultIndex'] ?? 0,
            tabsCount: count($tabs),
        );
        
        return view('components.sections.tabs-block', [
            ...$data,
            'tabs' => $tabs,
            'tabsHtml' => $tabsHtml,
            'defaultIndex' => $defaultIndexSafe,
        ])->render();
    }
    
    private function normalizeTabs(mixed $tabs): array
    {
        return is_array($tabs) ? $tabs : [];
    }
    
    private function resolveDefaultIndex(mixed $defaultIndex, int $tabsCount): int
    {
        $default = (int) $defaultIndex;
        
        if ($default < 0) {
            $default = 0;
        }
        
        if ($tabsCount > 0 && $default > $tabsCount - 1) {
            $default = 0;
        }
        
        return $default;
    }
}