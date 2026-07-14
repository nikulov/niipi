<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\TextFullRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class TextFullRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('text-full', TextFullRenderer::key());
        $this->assertSame('1', TextFullRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new TextFullRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
