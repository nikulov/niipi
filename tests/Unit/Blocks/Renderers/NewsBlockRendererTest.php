<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\NewsBlockRenderer;
use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsBlockRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_block_with_cards(): void
    {
        Post::create([
            'title' => 'Post A',
            'description' => 'Desc',
            'slug' => 'post-a',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $renderer = app(NewsBlockRenderer::class);

        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array { return []; }
            public function getRenderCacheId(): string { return 'page:1'; }
            public function getRenderUpdatedAtTimestamp(): int { return 0; }
        };

        $html = $renderer->render([
            'bgImageUrl' => '/bg.jpg',
            'title' => 'News',
            'btnUrl' => '/news',
            'btnLabel' => 'All news',
        ], $model, 0);

        $this->assertStringContainsString('News', $html);
        $this->assertStringContainsString('Post A', $html);
        $this->assertStringContainsString('All news', $html);
    }
}
