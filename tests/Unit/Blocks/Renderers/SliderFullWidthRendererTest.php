<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\SliderFullWidthRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class SliderFullWidthRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('slider-full-width', SliderFullWidthRenderer::key());
        $this->assertSame('1', SliderFullWidthRenderer::version());
    }

    public function test_render_returns_string_with_empty_sliders(): void
    {
        $html = (new SliderFullWidthRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
