<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\CardsBlockWithImageTitleRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class CardsBlockWithImageTitleRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('cards-block-with-image-title', CardsBlockWithImageTitleRenderer::key());
        $this->assertSame('1', CardsBlockWithImageTitleRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new CardsBlockWithImageTitleRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
