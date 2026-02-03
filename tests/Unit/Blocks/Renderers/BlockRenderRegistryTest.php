<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\BlockRenderRegistry;
use App\Blocks\Renderers\FormRenderer;
use App\Blocks\Renderers\NewsBlockRenderer;
use App\Blocks\Renderers\NewsFullRenderer;
use App\Blocks\Renderers\ProjectsBlockRenderer;
use App\Blocks\Renderers\ProjectsFullRenderer;
use Tests\TestCase;

class BlockRenderRegistryTest extends TestCase
{
    public function test_registry_contains_known_renderers(): void
    {
        $map = BlockRenderRegistry::map();

        $this->assertSame(NewsBlockRenderer::class, $map[NewsBlockRenderer::key()]);
        $this->assertSame(ProjectsBlockRenderer::class, $map[ProjectsBlockRenderer::key()]);
        $this->assertSame(NewsFullRenderer::class, $map[NewsFullRenderer::key()]);
        $this->assertSame(ProjectsFullRenderer::class, $map[ProjectsFullRenderer::key()]);
        $this->assertSame(FormRenderer::class, $map[FormRenderer::key()]);
    }
}
