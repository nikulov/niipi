<?php

namespace Tests\Unit\Models;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_relation(): void
    {
        $post = Post::create([
            'title' => 'Post',
            'description' => 'Post description',
            'slug' => 'post-1',
            'status' => PostStatus::Draft->value,
        ]);

        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'status' => 'active',
            'type' => 'posts',
        ]);

        $post->categories()->attach($category->id);

        $this->assertSame(1, $post->categories()->count());
    }

    public function test_get_blocks_for_section_and_all(): void
    {
        $post = Post::create([
            'title' => 'Post',
            'description' => 'Post description',
            'slug' => 'post-2',
            'status' => PostStatus::Draft->value,
            'top_section' => [['type' => 'a']],
            'main_section' => [['type' => 'b']],
            'bottom_section' => [['type' => 'c']],
        ]);

        $this->assertSame([['type' => 'a']], $post->getBlocksForSection('top'));
        $this->assertSame([['type' => 'b']], $post->getBlocksForSection('main'));
        $this->assertSame([['type' => 'c']], $post->getBlocksForSection('bottom'));
        $this->assertSame([['type' => 'a'], ['type' => 'b'], ['type' => 'c']], $post->getBlocksForSection(null));
    }

    public function test_scope_published_filters_by_status_and_date(): void
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

        $results = Post::query()->published()->pluck('id')->all();

        $this->assertSame([$published->id], $results);
    }

    public function test_meta_uses_meta_title_fallback(): void
    {
        $post = new Post([
            'title' => 'Title',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Desc',
        ]);

        $meta = $post->meta();

        $this->assertSame('Meta Title', $meta['title']);
        $this->assertSame('Meta Desc', $meta['description']);
        $this->assertArrayHasKey('keywords', $meta);
    }
}
