<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\CardsBlockWithButtonRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class CardsBlockWithButtonRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('cards-block-with-button', CardsBlockWithButtonRenderer::key());
        $this->assertSame('1', CardsBlockWithButtonRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new CardsBlockWithButtonRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
