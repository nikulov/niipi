<?php

namespace App\Livewire\Components;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Presenters\Blocks\NewsFullPresenter;
use App\Services\NewsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

final class NewsFull extends Component
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
        
        $cacheKey = 'news.categories:' . md5(json_encode($ids));
        
        return cache()
            ->tags(['news', 'categories'])
            ->remember($cacheKey, 600, function () use ($ids) {
                return Category::query()
                    ->posts()
                    ->when(
                        is_array($ids),
                        fn ($q) => $q->whereIn('id', $ids)
                    )
                    ->withCount([
                        'posts as posts_count' => fn ($q) =>
                        $q->where('status', PostStatus::Published),
                    ])
                    ->having('posts_count', '>', 0)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);
            });
    }
    
    private function getCards(NewsQuery $newsQuery, Collection $categories): LengthAwarePaginator
    {
        $selectedId = $this->category
            ? $categories->firstWhere('slug', $this->category)?->id
            : null;
        
        $filterIds = $selectedId ? [$selectedId] : $this->categoryIds;
        
        return $newsQuery
            ->list($this->limit, $filterIds, true)
            ->through(fn ($post) => NewsFullPresenter::make($post));
    }
    
    public function render(NewsQuery $newsQuery)
    {
        $categories = $this->getCategories();
        $cards = $this->getCards($newsQuery, $categories);
        
        $totalPostsCount = $categories->sum('posts_count');
        
        return view('livewire.components.news-full', [
            'categories' => $categories,
            'cards' => $cards,
            'activeCategory' => $this->category,
            'totalPostsCount' => $totalPostsCount,
        ]);
    }
}