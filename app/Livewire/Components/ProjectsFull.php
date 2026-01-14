<?php

namespace App\Livewire\Components;

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Presenters\Blocks\ProjectsFullPresenter;
use App\Services\ProjectsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

final class ProjectsFull extends Component
{
    use WithPagination;
    
    public int $limit = 10;
    
    /** @var array<int>|null */
    public ?array $categoryIds = null;
    
    public ?string $category = null;
    
    protected $queryString = [
        'category' => ['except' => null],
    ];
    
    public function mount(int $limit = 10, ?array $categoryIds = null): void
    {
        $this->limit = $limit;
        $this->categoryIds = $categoryIds;
        
        $this->normalizeCategory();
    }
    
    public function setCategory(?string $slug): void
    {
        $this->category = $slug ?: null;
    }
    
    public function updatedCategory(): void
    {
        $this->normalizeCategory();
        $this->resetPage();
    }
    
    private function normalizeCategory(): void
    {
        if (!$this->category) {
            return;
        }
        
        $exists = $this->getCategories()->contains('slug', $this->category);
        
        if (!$exists) {
            $this->category = null;
        }
    }
    
    private function getCategories(): Collection
    {
        $ids = is_array($this->categoryIds) && count($this->categoryIds) > 0
            ? array_values($this->categoryIds)
            : null;
        
        $cacheKey = 'projects.categories:' . md5(json_encode($ids));
        
        return cache()
            ->tags(['projects', 'categories'])
            ->remember($cacheKey, 600, function () use ($ids) {
                return Category::query()
                    ->projects()
                    ->when(
                        is_array($ids),
                        fn ($q) => $q->whereIn('id', $ids)
                    )
                    ->withCount([
                        'projects as projects_count' => fn ($q) =>
                        $q->where('status', ProjectStatus::Published),
                    ])
                    ->having('projects_count', '>', 0)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);
            });
    }
    
    private function getCards(ProjectsQuery $projectsQuery, Collection $categories): LengthAwarePaginator
    {
        $selectedId = $this->category
            ? $categories->firstWhere('slug', $this->category)?->id
            : null;
        
        $filterIds = $selectedId ? [$selectedId] : $this->categoryIds;
        
        return $projectsQuery
            ->list($this->limit, $filterIds, true)
            ->through(fn ($projects) => ProjectsFullPresenter::make($projects));
    }
    
    public function render(ProjectsQuery $projectsQuery)
    {
        $categories = $this->getCategories();
        $cards = $this->getCards($projectsQuery, $categories);
        
        $totalProjectsCount = $categories->sum('projects_count');
        
        return view('livewire.components.projects-full', [
            'categories' => $categories,
            'cards' => $cards,
            'activeCategory' => $this->category,
            'totalProjectsCount' => $totalProjectsCount,
        ]);
    }
}