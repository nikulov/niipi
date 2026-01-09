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
    public static function key(): string { return 'projects-block'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $limit = (int) ($data['limit'] ?? 4);
        
        $projects = $this->projectsQuery->list($limit, null, false);
        
        $cards = $projects->map(fn ($project) => ProjectsBlockPresenter::make($project))->toArray();
        
        return view('components.sections.projects-block', [
            'data' => $data,
            'cards' => $cards,
        ])->render();
    }
}