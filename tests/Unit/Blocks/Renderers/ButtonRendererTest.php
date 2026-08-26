<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\ButtonRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class ButtonRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('button', ButtonRenderer::key());
        $this->assertSame('1', ButtonRenderer::version());
    }

    public function test_renders_button_with_custom_label_and_url(): void
    {
        $html = (new ButtonRenderer())->render([
            'btnLabel' => 'Read more',
            'btnUrl' => '/read',
        ], new StubHasBlockSections(), 0);

        $this->assertStringContainsString('Read more', $html);
        $this->assertStringContainsString('/read', $html);
    }
}
