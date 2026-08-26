<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\AnchorRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class AnchorRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('anchor', AnchorRenderer::key());
        $this->assertSame('1', AnchorRenderer::version());
    }

    public function test_renders_div_with_id_when_anchor_filled(): void
    {
        $html = (new AnchorRenderer)->render(
            ['anchor' => 'my-section'],
            new StubHasBlockSections,
            0,
        );

        $this->assertStringContainsString('<div id="my-section"></div>', $html);
    }

    public function test_renders_nothing_when_anchor_blank(): void
    {
        $html = (new AnchorRenderer)->render([], new StubHasBlockSections, 0);

        $this->assertStringNotContainsString('<div', $html);
    }

    public function test_escapes_anchor_value(): void
    {
        $html = (new AnchorRenderer)->render(
            ['anchor' => '"><script>alert(1)</script>'],
            new StubHasBlockSections,
            0,
        );

        $this->assertStringNotContainsString('<script>', $html);
    }
}
