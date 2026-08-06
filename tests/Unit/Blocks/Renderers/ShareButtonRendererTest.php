<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\ShareButtonRenderer;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class ShareButtonRendererTest extends TestCase
{
    public function test_key_and_version(): void
    {
        $this->assertSame('share-button', ShareButtonRenderer::key());
        $this->assertSame('1', ShareButtonRenderer::version());
    }

    public function test_renders_button_with_custom_label_and_url(): void
    {
        $html = (new ShareButtonRenderer)->render([
            'btnLabel' => 'Read more',
            'btnUrl' => '/read',
        ], new StubHasBlockSections, 0);

        $this->assertStringContainsString('Read more', $html);
        $this->assertStringContainsString('/read', $html);
    }

    public function test_share_targets_are_templates_filled_in_by_the_browser(): void
    {
        $html = (new ShareButtonRenderer)->render([], new StubHasBlockSections, 0);

        $this->assertStringContainsString('vk.com/share.php?url={url}', $html);
        $this->assertStringContainsString('t.me/share/url?url={url}', $html);
        $this->assertStringContainsString('max.ru/:share?text={url}', $html);
    }

    public function test_copy_link_button_is_always_rendered(): void
    {
        $html = (new ShareButtonRenderer)->render([], new StubHasBlockSections, 0);

        $this->assertStringContainsString('navigator.clipboard', $html);
    }
}
