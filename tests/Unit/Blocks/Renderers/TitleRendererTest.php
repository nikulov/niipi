<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\TitleRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class TitleRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('title', TitleRenderer::key());
        $this->assertSame('1', TitleRenderer::version());
    }

    public function test_renders_title_with_custom_text(): void
    {
        $html = (new TitleRenderer())->render([
            'title' => 'Hello world',
        ], new StubHasBlockSections(), 0);

        $this->assertStringContainsString('Hello world', $html);
    }
}
