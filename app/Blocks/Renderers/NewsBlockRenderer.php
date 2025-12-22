<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Services\NewsQuery;

final class NewsBlockRenderer implements BlockRenderer
{
    public function __construct(
        private readonly NewsQuery $newsQuery,
    ) {}
    public static function key(): string { return 'news-block'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $limit = (int) ($data['limit'] ?? 4);
        
        $posts = $this->newsQuery->latest($limit, null);
        
        return view('components.sections.news-block', [
            'data' => $data,
            'posts' => $posts,
        ])->render();
    }
    
}