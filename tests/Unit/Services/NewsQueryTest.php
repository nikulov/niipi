<?php

namespace Tests\Unit\Services;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Services\NewsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class NewsQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_returns_only_published_and_not_future(): void
    {
        $published = Post::create([
            'title' => 'Published',
            'description' => 'Desc',
            'slug' => 'published',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        Post::create([
            'title' => 'Draft',
            'description' => 'Desc',
            'slug' => 'draft',
            'status' => PostStatus::Draft->value,
            'published_at' => now()->subDay(),
        ]);

        Post::create([
            'title' => 'Future',
            'description' => 'Desc',
            'slug' => 'future',
            'status' => PostStatus::Published->value,
            'published_at' => now()->addDay(),
        ]);

        $service = new NewsQuery;
        $items = $service->list(10);

        $this->assertCount(1, $items);
        $this->assertSame($published->id, $items->first()->id);
    }

    public function test_list_applies_limit_and_excludes_given_id(): void
    {
        $newest = $this->publishedPost('Newest', 'newest', now()->subDay());
        $middle = $this->publishedPost('Middle', 'middle', now()->subDays(2));
        $this->publishedPost('Oldest', 'oldest', now()->subDays(3));

        $service = new NewsQuery;

        $limited = $service->list(2);
        $this->assertCount(2, $limited);
        $this->assertSame($newest->id, $limited->first()->id);

        $withoutNewest = $service->list(10, null, false, 'page', $newest->id);
        $this->assertCount(2, $withoutNewest);
        $this->assertSame($middle->id, $withoutNewest->first()->id);
    }

    public function test_list_filters_by_category_and_can_paginate(): void
    {
        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);

        $postA = Post::create([
            'title' => 'Post A',
            'description' => 'Desc',
            'slug' => 'post-a',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $postB = Post::create([
            'title' => 'Post B',
            'description' => 'Desc',
            'slug' => 'post-b',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $postA->categories()->attach($category->id);

        $service = new NewsQuery;

        $filtered = $service->list(10, [$category->id]);
        $this->assertCount(1, $filtered);
        $this->assertSame($postA->id, $filtered->first()->id);

        $paginated = $service->list(1, null, true);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);
        $this->assertSame(1, $paginated->perPage());
        $this->assertSame(2, $paginated->total());
    }

    private function publishedPost(string $title, string $slug, $publishedAt): Post
    {
        return Post::create([
            'title' => $title,
            'description' => 'Desc',
            'slug' => $slug,
            'status' => PostStatus::Published->value,
            'published_at' => $publishedAt,
        ]);
    }
}
