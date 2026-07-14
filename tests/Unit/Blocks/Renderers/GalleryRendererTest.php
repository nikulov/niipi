<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\GalleryRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class GalleryRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('gallery', GalleryRenderer::key());
        $this->assertSame('1', GalleryRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new GalleryRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
