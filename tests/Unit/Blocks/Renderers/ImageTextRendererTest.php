<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\ImageTextRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class ImageTextRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('image-text', ImageTextRenderer::key());
        $this->assertSame('1', ImageTextRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new ImageTextRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
