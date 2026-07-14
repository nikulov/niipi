<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\TabsBlockRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class TabsBlockRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('tabs-block', TabsBlockRenderer::key());
        $this->assertSame('2', TabsBlockRenderer::version());
    }

    public function test_renders_with_empty_tabs(): void
    {
        $html = (new TabsBlockRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }

    public function test_renders_with_tabs_content(): void
    {
        $html = (new TabsBlockRenderer())->render([
            'tabs' => [
                ['title' => 'Tab 1', 'tab' => []],
                ['title' => 'Tab 2', 'tab' => []],
            ],
            'defaultIndex' => 5,
        ], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
        $this->assertStringContainsString('Tab 1', $html);
    }
}
