<?php

namespace App\Services;

use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


final class NewsQuery
{
    public function list(
        int $perPageOrLimit = 4,
        ?array $categoryIds = null,
        bool $paginate = false,
        string $pageName = 'page'
    ): Collection|LengthAwarePaginator {
        $query = Post::query()
            ->with('categories')
            ->where('status', PostStatus::Published->value)
            ->orderByDesc('published_at');
        
        if ($categoryIds && $categoryIds !== []) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        }
        
        if (!$paginate) {
            return $query->limit($perPageOrLimit)->get();
        }
        
        return $query->paginate($perPageOrLimit, ['*'], $pageName)->withQueryString();
    }
}