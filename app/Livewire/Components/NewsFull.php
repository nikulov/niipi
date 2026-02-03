<?php

namespace App\Livewire\Components;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Presenters\Blocks\NewsFullPresenter;
use App\Services\NewsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class NewsFull extends AbstractContentFull
{
    protected $queryString = [
        'category' => ['except' => null, 'as' => 'newsCategory'],
    ];

    protected function buildCategoriesQuery(?array $ids): Builder
    {
        return Category::query()
            ->posts()
            ->when(
                is_array($ids),
                fn ($q) => $q->whereIn('id', $ids)
            )
            ->withCount([
                'posts as posts_count' => fn ($q) =>
                $q->where('status', PostStatus::Published->value),
            ]);
    }

    protected function getCacheKey(): string
    {
        return 'news.categories';
    }

    protected function getCacheTags(): array
    {
        return ['news', 'categories'];
    }

    protected function getCountColumn(): string
    {
        return 'posts_count';
    }

    protected function getPivotTable(): string
    {
        return 'category_post';
    }

    protected function getPivotForeignKey(): string
    {
        return 'post_id';
    }

    protected function queryString(): array
    {
        return $this->queryString;
    }
    
    protected function getContentTable(): string
    {
        return 'posts';
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
        return PostStatus::Published->value;
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
        $totalCount = $this->getTotalCount($categories);
        $categoryItems = $this->buildCategoryItems($categories, $totalCount);

        return view('livewire.components.news-full', [
            'categories' => $categories,
            'cards' => $cards,
            'activeCategory' => $this->category,
            'categoryItems' => $categoryItems,
        ]);
    }
}
