<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\InfoBlockWithAchievementsRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class InfoBlockWithAchievementsRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('info-block-with-achievements', InfoBlockWithAchievementsRenderer::key());
        $this->assertSame('1', InfoBlockWithAchievementsRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new InfoBlockWithAchievementsRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
