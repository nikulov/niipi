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
}
