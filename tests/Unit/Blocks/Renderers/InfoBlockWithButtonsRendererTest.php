<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\InfoBlockWithButtonsRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class InfoBlockWithButtonsRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('info-block-with-buttons', InfoBlockWithButtonsRenderer::key());
        $this->assertSame('1', InfoBlockWithButtonsRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new InfoBlockWithButtonsRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
