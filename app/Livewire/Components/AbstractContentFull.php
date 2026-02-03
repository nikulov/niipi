<?php

namespace App\Livewire\Components;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractContentFull extends Component
{
    use WithPagination;

    public int $limit = 10;

    /** @var array<int>|null */
    public ?array $categoryIds = null;

    public ?string $category = null;

    public ?string $componentKey = null;

    private ?Collection $categoriesCache = null;
    abstract protected function getContentTable(): string;
    abstract protected function getContentPrimaryKey(): string;
    abstract protected function getStatusColumn(): string;
    abstract protected function getPublishedStatusValue(): string|int;

    public function mount(int $limit = 10, ?array $categoryIds = null, ?string $componentKey = null): void
    {
        $this->limit = $limit;
        $this->categoryIds = is_array($categoryIds) ? array_values($categoryIds) : null;
        $this->componentKey = $componentKey;

        $this->normalizeCategory();
    }

    public function getPageName(): string
    {
        return $this->componentKey
            ? 'page_' . md5($this->componentKey)
            : 'page';
    }

    public function setCategory(?string $slug): void
    {
        $this->category = $slug ?: null;
        $this->normalizeCategory();
        $this->resetPage($this->getPageName());
    }

    private function normalizeCategory(): void
    {
        if (!$this->category) {
            return;
        }

        if (!$this->categories()->contains('slug', $this->category)) {
            $this->category = null;
        }
    }

    protected function getCategories(): Collection
    {
        $ids = is_array($this->categoryIds) && count($this->categoryIds) > 0
            ? array_values($this->categoryIds)
            : null;

        $cacheKey = $this->getCacheKey() . ':' . md5(json_encode($ids));

        return cache()
            ->tags($this->getCacheTags())
            ->remember($cacheKey, 600, function () use ($ids) {
                return $this->buildCategoriesQuery($ids)
                    ->having($this->getCountColumn(), '>', 0)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);
            });
    }

    protected function categories(): Collection
    {
        return $this->categoriesCache ??= $this->getCategories();
    }

    protected function buildCategoryItems(Collection $categories, int $totalCount): Collection
    {
        return collect([
            [
                'slug' => null,
                'name' => __('panel.all'),
                'count' => $totalCount,
            ],
            ...$categories->map(fn ($cat) => [
                'slug' => $cat->slug,
                'name' => $cat->name,
                'count' => $cat->{$this->getCountColumn()},
            ])->all(),
        ]);
    }
    
    protected function getTotalCount(Collection $categories): int
    {
        $categoryIds = $categories->pluck('id')->values();
        
        if ($categoryIds->isEmpty()) {
            return 0;
        }
        
        $pivot = $this->getPivotTable();
        $fk = $this->getPivotForeignKey();
        
        $contentTable = $this->getContentTable();
        $contentPk = $this->getContentPrimaryKey();
        $statusCol = $this->getStatusColumn();
        $published = $this->getPublishedStatusValue();
        
        return DB::table($pivot)
            ->join($contentTable, $contentTable . '.' . $contentPk, '=', $pivot . '.' . $fk)
            ->whereIn($pivot . '.category_id', $categoryIds)
            ->where($contentTable . '.' . $statusCol, '=', $published)
            ->distinct()
            ->count($pivot . '.' . $fk);
    }

    /**
     * Build categories query with count
     */
    abstract protected function buildCategoriesQuery(?array $ids): Builder;

    /**
     * Get cache key prefix (e.g., 'news.categories', 'projects.categories')
     */
    abstract protected function getCacheKey(): string;

    /**
     * Get cache tags (e.g., ['news', 'categories'])
     */
    abstract protected function getCacheTags(): array;

    /**
     * Get count column name (e.g., 'posts_count', 'projects_count')
     */
    abstract protected function getCountColumn(): string;

    /**
     * Get pivot table name (e.g., 'category_post', 'category_project')
     */
    abstract protected function getPivotTable(): string;

    /**
     * Get pivot foreign key (e.g., 'post_id', 'project_id')
     */
    abstract protected function getPivotForeignKey(): string;

    /**
     * Get query string configuration for category filter
     */
    abstract protected function queryString(): array;
}
