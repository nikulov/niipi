<?php

namespace App\Livewire\Components;

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Presenters\Blocks\ProjectsFullPresenter;
use App\Services\ProjectsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ProjectsFull extends AbstractContentFull
{
    protected $queryString = [
        'category' => ['except' => null, 'as' => 'projectsCategory'],
    ];

    protected function buildCategoriesQuery(?array $ids): Builder
    {
        return Category::query()
            ->projects()
            ->when(
                is_array($ids),
                fn ($q) => $q->whereIn('id', $ids)
            )
            ->withCount([
                'projects as projects_count' => fn ($q) =>
                $q->where('status', ProjectStatus::Published->value),
            ]);
    }

    protected function getCacheKey(): string
    {
        return 'projects.categories';
    }

    protected function getCacheTags(): array
    {
        return ['projects', 'categories'];
    }

    protected function getCountColumn(): string
    {
        return 'projects_count';
    }

    protected function getPivotTable(): string
    {
        return 'category_project';
    }

    protected function getPivotForeignKey(): string
    {
        return 'project_id';
    }

    protected function queryString(): array
    {
        return $this->queryString;
    }
    
    protected function getContentTable(): string
    {
        return 'projects';
    }
    
    protected function getContentPrimaryKey(): string
    {
        return 'id';
    }
    
    protected function getStatusColumn(): string
    {
        return 'status';
    }
    
    protected function getPublishedStatusValue(): string|int
    {
        return ProjectStatus::Published->value;
    }

    private function getCards(ProjectsQuery $projectsQuery, Collection $categories): LengthAwarePaginator
    {
        $selectedId = $this->category
            ? $categories->firstWhere('slug', $this->category)?->id
            : null;

        $filterIds = $selectedId ? [$selectedId] : $this->categoryIds;

        return $projectsQuery
            ->list($this->limit, $filterIds, true, $this->getPageName())
            ->through(fn ($project) => ProjectsFullPresenter::make($project));
    }

    public function render(ProjectsQuery $projectsQuery)
    {
        $categories = $this->getCategories();
        $cards = $this->getCards($projectsQuery, $categories);
        $totalCount = $this->getTotalCount($categories);
        $categoryItems = $this->buildCategoryItems($categories, $totalCount);

        return view('livewire.components.projects-full', [
            'categories' => $categories,
            'cards' => $cards,
            'activeCategory' => $this->category,
            'categoryItems' => $categoryItems,
        ]);
    }
}
