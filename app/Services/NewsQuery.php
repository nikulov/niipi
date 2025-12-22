<?php

namespace App\Services;

use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class NewsQuery
{
    public function list(int $perPageOrLimit = 4, ?array $categoryIds = null, bool $paginate = false): Collection|LengthAwarePaginator
    {
        $query = Post::query()
            ->with('categories')
            ->where('status', PostStatus::Published)
            ->orderByDesc('published_at');
        
        if ($categoryIds && $categoryIds !== []) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        }
        
        return $paginate ? $query->paginate($perPageOrLimit)->withQueryString() : $query->limit($perPageOrLimit)->get();
    }
}