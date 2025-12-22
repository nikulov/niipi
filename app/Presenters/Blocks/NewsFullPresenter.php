<?php

namespace App\Presenters\Blocks;

use App\Models\Post;

final class NewsFullPresenter
{
    public static function make(Post $post): array
    {
        return [
            'title' => $post->title,
            'description' => $post->description,
            'url' => url('news/' . $post->slug),
            'publishedAt' => $post->published_at?->format('d.m.Y'),
            'thumbnail' => public_asset($post->thumbnail),
            'categories' => $post->categories->map(static fn ($category) => [
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => url('news/category/' . $category->slug),
            ])->toArray(),
        ];
    }
}