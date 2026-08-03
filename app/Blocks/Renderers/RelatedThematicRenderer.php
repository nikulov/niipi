<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Models\Post;
use App\Models\Project;
use App\Presenters\Blocks\NewsBlockPresenter;
use App\Presenters\Blocks\ProjectsBlockPresenter;
use App\Services\NewsQuery;
use App\Services\ProjectsQuery;

final class RelatedThematicRenderer implements BlockRenderer
{
    public function __construct(
        private readonly NewsQuery $newsQuery,
        private readonly ProjectsQuery $projectsQuery,
    ) {}

    public static function key(): string
    {
        return 'related-thematic';
    }

    public static function version(): string
    {
        return '1';
    }

    public function render(array $data, HasBlockSections $model, int $index): string
    {
        if (! $model instanceof Post && ! $model instanceof Project) {
            return '';
        }

        $limit = max(1, min(20, (int) ($data['limit'] ?? 5)));

        $overrideIds = $data['categoryIds'] ?? null;
        $overrideIds = is_array($overrideIds) && $overrideIds !== []
            ? array_values(array_map('intval', $overrideIds))
            : null;

        $modelCategories = $model->categories()->orderBy('name')->get();

        $categoryIds = $overrideIds ?? $modelCategories->pluck('id')->all();

        if (empty($categoryIds)) {
            return '';
        }

        $isPost = $model instanceof Post;

        $items = $isPost
            ? $this->newsQuery->list($limit, $categoryIds, false, 'page', $model->getKey())
            : $this->projectsQuery->list($limit, $categoryIds, false, 'page', $model->getKey());

        if ($items->isEmpty()) {
            return '';
        }

        $cards = $items->map(fn ($m) => $isPost
            ? NewsBlockPresenter::make($m)
            : ProjectsBlockPresenter::make($m)
        )->toArray();

        $firstCategorySlug = $modelCategories->first()?->slug;
        $basePath = $isPost ? 'news' : 'projects';
        $queryParam = $isPost ? 'newsCategory' : 'projectsCategory';

        $btnUrl = url($basePath.($firstCategorySlug ? '?'.$queryParam.'='.urlencode($firstCategorySlug) : ''));

        return view('components.sections.related-thematic', [
            'title' => $data['title'] ?? '',
            'btnLabel' => $data['btnLabel'] ?? __('panel.related_thematic_all_btn'),
            'btnUrl' => $btnUrl,
            'cards' => $cards,
            'isPost' => $isPost,
        ])->render();
    }
}
