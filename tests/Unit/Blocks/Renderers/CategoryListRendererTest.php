<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\CategoryListRenderer;
use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class CategoryListRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_and_version(): void
    {
        $this->assertSame('category-list', CategoryListRenderer::key());
        $this->assertSame('1', CategoryListRenderer::version());
    }

    public function test_returns_empty_for_unsupported_model(): void
    {
        $html = (new CategoryListRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertSame('', $html);
    }

    public function test_renders_categories_for_post(): void
    {
        $post = Post::create([
            'title' => 'P',
            'description' => 'D',
            'slug' => 'p',
            'status' => PostStatus::Published->value,
            'published_at' => now(),
        ]);

        $cat = Category::create([
            'name' => 'Строительство',
            'slug' => 'stroy',
        ]);

        $post->categories()->attach($cat->id);

        $html = (new CategoryListRenderer())->render([], $post, 0);

        $this->assertStringContainsString('Строительство', $html);
    }
}
