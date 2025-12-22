<?php

namespace App\Services;

use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Collection;

final class NewsQuery
{
    public function latest(int $limit = 4, ?array $categoryIds = null): Collection
    {
        $query = Post::query()
            ->with('categories')
            ->where('status', PostStatus::Published)
            ->orderByDesc('published_at');
        
        if ($categoryIds && $categoryIds !== []) {
            $query->whereHas(
                'categories',
                fn ($q) => $q->whereIn('categories.id', $categoryIds)
            );
        }
        
        return $query->limit($limit)->get();
    }
}