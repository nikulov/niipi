<?php

namespace Tests\Unit\Presenters\Blocks;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Presenters\Blocks\NewsBlockPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsBlockPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_builds_card_data(): void
    {
        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);

        $post = Post::create([
            'title' => 'Post',
            'description' => 'Desc',
            'slug' => 'post-1',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $post->categories()->attach($category->id);
        $post->load('categories');

        $card = NewsBlockPresenter::make($post);

        $this->assertSame('Post', $card['title']);
        $this->assertSame('Desc', $card['description']);
        $this->assertStringContainsString('/news/post-1', $card['url']);
        $this->assertSame(1, count($card['categories']));
        $this->assertSame('news', $card['categories'][0]['slug']);
        $this->assertStringContainsString('/news/category/news', $card['categories'][0]['url']);
    }
}
