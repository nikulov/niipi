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
            ->select(['id', 'title','description', 'slug', 'thumbnail', 'published_at'])
            ->with(['categories:id,name,slug'])
            ->where('status', PostStatus::Published)
            ->orderByDesc('published_at');
        
        if ($categoryIds && $categoryIds !== []) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }
        
        return $query->limit($limit)->get();
    }
}