<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\NewsFullRenderer;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class NewsFullRendererTest extends TestCase
{
    public function test_renders_via_livewire_mount_with_normalized_args(): void
    {
        Livewire::shouldReceive('mount')
            ->once()
            ->withArgs(function ($component, $params, $key) {
                if ($component !== 'components.news-full') {
                    return false;
                }

                if (!is_string($key) || $key === '') {
                    return false;
                }

                return $params['limit'] === 50
                    && $params['categoryIds'] === null
                    && str_starts_with($params['componentKey'], 'block:news-full:page:1:');
            })
            ->andReturn('<div>news</div>');

        $renderer = new NewsFullRenderer();

        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array { return []; }
            public function getRenderCacheId(): string { return 'page:1'; }
            public function getRenderUpdatedAtTimestamp(): int { return 0; }
        };

        $html = $renderer->render([
            'limit' => 999,
            'categoryIds' => [],
        ], $model, 0);

        $this->assertSame('<div>news</div>', $html);
    }
}
