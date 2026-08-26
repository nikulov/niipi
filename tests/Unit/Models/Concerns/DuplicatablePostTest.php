<?php

namespace Tests\Unit\Models\Concerns;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicatablePostTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_appends_kopiya_suffix_and_resets_status(): void
    {
        $post = Post::create([
            'title' => 'Оригинал',
            'description' => 'desc',
            'slug' => 'original',
            'status' => PostStatus::Published->value,
            'published_at' => now(),
        ]);

        $copy = $post->duplicate();

        $this->assertSame('Оригинал (копия)', $copy->title);
        $this->assertSame('original-copy', $copy->slug);
        $this->assertSame(PostStatus::Draft, $copy->status);
        $this->assertNull($copy->published_at);
    }

    public function test_duplicate_copies_categories_pivot(): void
    {
        $post = Post::create([
            'title' => 'Post',
            'description' => 'desc',
            'slug' => 'post-cat',
            'status' => PostStatus::Draft->value,
        ]);
        $cat = Category::create([
            'name' => 'News',
            'slug' => 'news-cat',
            'status' => 'active',
            'type' => 'posts',
        ]);
        $post->categories()->attach($cat->id);

        $copy = $post->duplicate();

        $this->assertSame([$cat->id], $copy->categories()->pluck('categories.id')->all());
    }

    public function test_repeated_duplicate_increments_counter(): void
    {
        $post = Post::create([
            'title' => 'X',
            'description' => 'desc',
            'slug' => 'x',
            'status' => PostStatus::Draft->value,
        ]);

        $post->duplicate();
        $post->duplicate();
        $third = $post->duplicate();

        $this->assertSame('X (копия 3)', $third->title);
        $this->assertSame('x-copy-3', $third->slug);
    }

    public function test_duplicate_of_a_copy_gets_next_number(): void
    {
        $post = Post::create([
            'title' => 'Y',
            'description' => 'desc',
            'slug' => 'y',
            'status' => PostStatus::Draft->value,
        ]);
        $first = $post->duplicate();

        $second = $first->duplicate();

        $this->assertSame('Y (копия 2)', $second->title);
        $this->assertSame('y-copy-2', $second->slug);
    }

    public function test_next_number_uses_slug_max_when_title_renamed(): void
    {
        $post = Post::create([
            'title' => 'Foo',
            'description' => 'desc',
            'slug' => 'foo',
            'status' => PostStatus::Draft->value,
        ]);
        $first = $post->duplicate();
        $first->update(['title' => 'Foo bar']);

        $second = $post->duplicate();

        $this->assertSame('Foo (копия 2)', $second->title);
        $this->assertSame('foo-copy-2', $second->slug);
    }
}
