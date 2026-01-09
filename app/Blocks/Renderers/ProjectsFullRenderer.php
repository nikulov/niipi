<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Presenters\Blocks\ProjectsFullPresenter;
use App\Services\ProjectsQuery;

final class ProjectsFullRenderer implements BlockRenderer
{
    public function __construct(
        private readonly ProjectsQuery $projectsQuery,
    ) {}
    public static function key(): string { return 'projects-full'; }
    public static function version(): string { return '1'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        
        $limit = (int) ($data['limit'] ?? 10);
        
        $categoryIds = $data['categoryIds'] ?? null;
        
        $paginator = $this->projectsQuery->list($limit, $categoryIds, true);
        
        $cards = $paginator->through(fn ($project) => ProjectsFullPresenter::make($project));
        
        return view('components.sections.projects-full', [
            'data' => $data,
            'cards' => $cards,
        ])->render();
    }
}