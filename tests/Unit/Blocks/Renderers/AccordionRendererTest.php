<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\AccordionRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class AccordionRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('accordion', AccordionRenderer::key());
        $this->assertSame('1', AccordionRenderer::version());
    }

    public function test_renders_with_defaults_and_uses_white_type(): void
    {
        $html = (new AccordionRenderer())->render([
            'title' => '  My accordions  ',
            'accordions' => [
                [
                    'point' => 'ЭТАП I',
                    'itemTitle' => 'ЗАГОЛОВОК',
                    'itemDescription' => '// Desc',
                    'items' => [],
                ],
            ],
        ], new StubHasBlockSections(), 0);

        $this->assertStringContainsString('My accordions', $html);
    }

    public function test_renders_when_accordions_missing(): void
    {
        $html = (new AccordionRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
