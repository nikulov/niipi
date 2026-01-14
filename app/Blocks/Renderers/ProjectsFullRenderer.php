<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use Livewire\Livewire;

final class ProjectsFullRenderer implements BlockRenderer
{
    public static function key(): string
    {
        return 'projects-full';
    }
    
    public static function version(): string
    {
        return '2';
    }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $limit = (int) ($data['limit'] ?? 10);
        $categoryIds = $data['categoryIds'] ?? null;
        
        $wireKey = sprintf(
            'block:%s:%s:%d',
            self::key(),
            $model->getRenderCacheId(),
            $index
        );
        
        $mounted = Livewire::mount(
            'components.projects-full',
            [
                'limit' => $limit,
                'categoryIds' => is_array($categoryIds) ? $categoryIds : null,
            ],
            $wireKey
        );
        
        if (is_string($mounted)) {
            return $mounted;
        }
        
        return method_exists($mounted, 'html') ? $mounted->html() : (string) $mounted;
    }
}