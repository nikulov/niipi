<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\YandexMapRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class YandexMapRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('yandex-map', YandexMapRenderer::key());
        $this->assertSame('1', YandexMapRenderer::version());
    }

    public function test_render_returns_string(): void
    {
        $html = (new YandexMapRenderer())->render([], new StubHasBlockSections(), 0);

        $this->assertIsString($html);
    }
}
