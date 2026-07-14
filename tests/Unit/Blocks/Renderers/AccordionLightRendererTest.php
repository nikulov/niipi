<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\AccordionLightRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class AccordionLightRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('accordion-light', AccordionLightRenderer::key());
        $this->assertSame('1', AccordionLightRenderer::version());
    }

    public function test_render_returns_html(): void
    {
        $html = (new AccordionLightRenderer())->render([
            'title' => 'Light title',
            'accordions' => [],
        ], new StubHasBlockSections(), 0);

        $this->assertStringContainsString('Light title', $html);
    }
}
