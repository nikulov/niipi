<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\ProjectsBlockRenderer;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsBlockRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_block_with_cards(): void
    {
        Project::create([
            'title' => 'Project A',
            'description' => 'Desc',
            'slug' => 'project-a',
            'thumbnail' => '/img.jpg',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $renderer = app(ProjectsBlockRenderer::class);

        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array { return []; }
            public function getRenderCacheId(): string { return 'page:1'; }
            public function getRenderUpdatedAtTimestamp(): int { return 0; }
        };

        $html = $renderer->render([
            'bgImageUrl' => '/bg.jpg',
            'title' => 'Projects',
            'btnUrl' => '/projects',
            'btnLabel' => 'All projects',
        ], $model, 0);

        $this->assertStringContainsString('Projects', $html);
        $this->assertStringContainsString('Project A', $html);
        $this->assertStringContainsString('All projects', $html);
    }
}
