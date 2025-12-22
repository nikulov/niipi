<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Presenters\Blocks\NewsFullPresenter;
use App\Services\NewsQuery;

final class NewsFullRenderer implements BlockRenderer
{
    public function __construct(
        private readonly NewsQuery $newsQuery,
    ) {}
    public static function key(): string { return 'news-full'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        
        $limit = (int) ($data['limit'] ?? 10);
        
        $categoryIds = $data['categoryIds'] ?? null;
        
        $paginator = $this->newsQuery->list($limit, $categoryIds, true);
        
        $cards = $paginator->through(fn ($post) => NewsFullPresenter::make($post));
        
        return view('components.sections.news-full', [
            'data' => $data,
            'cards' => $cards,
        ])->render();
    }
}