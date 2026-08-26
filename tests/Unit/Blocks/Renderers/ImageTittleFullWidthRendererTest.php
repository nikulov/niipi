<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\ImageTittleFullWidthRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class ImageTittleFullWidthRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('image-tittle-full-width', ImageTittleFullWidthRenderer::key());
        $this->assertSame('1', ImageTittleFullWidthRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new ImageTittleFullWidthRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
