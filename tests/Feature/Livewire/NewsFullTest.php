<?php

namespace Tests\Feature\Livewire;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Livewire\Components\NewsFull;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewsFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_category_resets_invalid_slug(): void
    {
        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);

        $post = Post::create([
            'title' => 'Post A',
            'description' => 'Desc',
            'slug' => 'post-a',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $post->categories()->attach($category->id);

        Livewire::test(NewsFull::class)
            ->call('setCategory', 'missing')
            ->assertSet('category', null);
    }

    public function test_category_and_total_counts_ignore_future_publications(): void
    {
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);

        $past = Post::create([
            'title' => 'Past',
            'description' => 'Desc',
            'slug' => 'past',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $future = Post::create([
            'title' => 'Future',
            'description' => 'Desc',
            'slug' => 'future',
            'status' => PostStatus::Published->value,
            'published_at' => now()->addDay(),
        ]);

        $past->categories()->attach($category->id);
        $future->categories()->attach($category->id);

        $items = Livewire::test(NewsFull::class)->viewData('categoryItems');

        $all = $items->firstWhere('slug', null);
        $tech = $items->firstWhere('slug', 'tech');

        $this->assertSame(1, $all['count']);
        $this->assertSame(1, $tech['count']);
    }
}
