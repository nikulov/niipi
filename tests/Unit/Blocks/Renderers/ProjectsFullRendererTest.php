<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\ProjectsFullRenderer;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectsFullRendererTest extends TestCase
{
    public function test_renders_via_livewire_mount_with_normalized_args(): void
    {
        Livewire::shouldReceive('mount')
            ->once()
            ->withArgs(function ($component, $params, $key) {
                if ($component !== 'components.projects-full') {
                    return false;
                }

                if (!is_string($key) || $key === '') {
                    return false;
                }

                return $params['limit'] === 1
                    && $params['categoryIds'] === [2, 1]
                    && str_starts_with($params['componentKey'], 'block:projects-full:page:1:');
            })
            ->andReturn('<div>projects</div>');

        $renderer = new ProjectsFullRenderer();

        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array { return []; }
            public function getRenderCacheId(): string { return 'page:1'; }
            public function getRenderUpdatedAtTimestamp(): int { return 0; }
        };

        $html = $renderer->render([
            'limit' => 0,
            'categoryIds' => [2, 1],
        ], $model, 0);

        $this->assertSame('<div>projects</div>', $html);
    }
}
