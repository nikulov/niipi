<?php

namespace App\Livewire\Components;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Presenters\Blocks\NewsFullPresenter;
use App\Services\NewsQuery;
use App\Services\ProjectsQuery;
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
    
    public ?string $componentKey = null;
    
    protected $queryString = [
        'category' => ['except' => null, 'as' => 'newsCategory'],
    ];
    
    private ?Collection $categoriesCache = null;
    
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
                        $q->where('status', PostStatus::Published->value),
                    ])
                    ->having('posts_count', '>', 0)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);
            });
    }
    
    private function categories(): Collection
    {
        return $this->categoriesCache ??= $this->getCategories();
    }
    
    public function getCategoryItemsProperty(): Collection
    {
        $categories = $this->categories();
        $totalPostsCount = (int) $categories->sum('posts_count');
        
        return collect([
            [
                'slug' => null,
                'name' => 'Все',
                'count' => $totalPostsCount,
            ],
            ...$categories->map(fn ($cat) => [
                'slug' => $cat->slug,
                'name' => $cat->name,
                'count' => (int) $cat->posts_count,
            ])->all(),
        ]);
    }
    
    private function getCards(NewsQuery $newsQuery, Collection $categories): LengthAwarePaginator
    {
        $selectedId = $this->category
            ? $categories->firstWhere('slug', $this->category)?->id
            : null;
        
        $filterIds = $selectedId ? [$selectedId] : $this->categoryIds;
        
        return $newsQuery
            ->list($this->limit, $filterIds, true, $this->getPageName())
            ->through(fn ($post) => NewsFullPresenter::make($post));
    }
    
    public function render(NewsQuery $newsQuery)
    {
        $categories = $this->getCategories();
        $cards = $this->getCards($newsQuery, $categories);
        
        $totalProjectsCount = $categories->sum('posts_count');
        
        $categoryItems = collect([
            [
                'slug' => null,
                'name' => 'Все',
                'count' => $totalProjectsCount,
            ],
            ...$categories->map(fn ($cat) => [
                'slug' => $cat->slug,
                'name' => $cat->name,
                'count' => $cat->posts_count,
            ])->all(),
        ]);
        
        return view('livewire.components.news-full', [
            'categories' => $categories,
            'cards' => $cards,
            'activeCategory' => $this->category,
            'categoryItems' => $categoryItems,
        ]);
    }
}