<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use Livewire\Livewire;

final class NewsFullRenderer implements BlockRenderer
{
    public static function key(): string
    {
        return 'news-full';
    }
    
    public static function version(): string
    {
        return '2';
    }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $limit = max(1, min(50, (int) ($data['limit'] ?? 10)));
        
        $categoryIds = $data['categoryIds'] ?? null;
        $categoryIds = is_array($categoryIds) ? array_values($categoryIds) : null;
        
        if (is_array($categoryIds) && count($categoryIds) === 0) {
            $categoryIds = null;
        }
        
        $wireKey = sprintf(
            'block:%s:%s:%d',
            self::key(),
            $model->getRenderCacheId(),
            $index
        );
        
        $mounted = Livewire::mount(
            'components.news-full',
            [
                'limit' => $limit,
                'categoryIds' => $categoryIds,
                'componentKey' => $wireKey,
            ],
            $wireKey
        );
        
        if (is_string($mounted)) {
            return $mounted;
        }
        
        return method_exists($mounted, 'html') ? $mounted->html() : (string) $mounted;
    }
}