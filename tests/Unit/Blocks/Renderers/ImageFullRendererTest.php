<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\ImageFullRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class ImageFullRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('image-full', ImageFullRenderer::key());
        $this->assertSame('1', ImageFullRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new ImageFullRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
