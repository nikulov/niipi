<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Presenters\Blocks\ProjectsBlockPresenter;
use App\Services\ProjectsQuery;

final class ProjectsBlockRenderer implements BlockRenderer
{
    public function __construct(
        private readonly ProjectsQuery $projectsQuery,
    ) {}

    public static function key(): string
    {
        return 'projects-block';
    }

    public static function version(): string
    {
        return '1';
    }

    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $limit = max(1, (int) ($data['limit'] ?? 4));

        $pinnedIds = array_filter(array_map('intval', (array) ($data['projectIds'] ?? [])));

        $pinned = $this->projectsQuery->byIds($pinnedIds)->take($limit);

        $projects = $pinned->count() < $limit
            ? $pinned->concat($this->projectsQuery->list($limit - $pinned->count(), null, false, 'page', $pinnedIds))
            : $pinned;

        $cards = $projects->map(fn ($project) => ProjectsBlockPresenter::make($project))->toArray();

        return view('components.sections.projects-block', [
            'data' => $data,
            'cards' => $cards,
        ])->render();
    }
}
