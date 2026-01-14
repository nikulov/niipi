<?php

namespace App\Livewire\Components;

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
    }
    
    public function setCategory(?string $slug): void
    {
        $this->category = $slug ?: null;
        $this->resetPage();
    }
    
    private function getCategories(): Collection
    {
        return Category::query()
            ->posts()
            ->when(
                is_array($this->categoryIds) && count($this->categoryIds) > 0,
                fn($q) => $q->whereIn('id', $this->categoryIds)
            )
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }
    
    private function getCards(NewsQuery $newsQuery, Collection $categories): LengthAwarePaginator
    {
        $selectedId = null;
        
        if ($this->category) {
            $selectedId = $categories->firstWhere('slug', $this->category)?->id;
        }
        
        // If slug is invalid (not in allowed categories) reset filter.
        if ($this->category && !$selectedId) {
            $this->category = null;
            $this->resetPage();
        }
        
        $filterIds = $selectedId ? [$selectedId] : $this->categoryIds;
        
        $paginator = $newsQuery->list($this->limit, $filterIds, true);
        
        /** @var LengthAwarePaginator $cards */
        $cards = $paginator->through(fn($post) => NewsFullPresenter::make($post));
        
        return $cards;
    }
    
    public function render(NewsQuery $newsQuery)
    {
        $categories = $this->getCategories();
        $cards = $this->getCards($newsQuery, $categories);
        
        return view('livewire.components.news-full', [
            'categories' => $categories,
            'cards' => $cards,
            'activeCategory' => $this->category,
        ]);
    }
}