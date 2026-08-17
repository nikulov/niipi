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

    public function test_renders_a_link_per_configured_social(): void
    {
        $html = (new ShareButtonRenderer)->render([
            'socials' => [
                ['iconUrl' => 'images/share/vk.svg', 'title' => 'ВКонтакте', 'shareUrl' => 'https://vk.com/share.php?url={url}'],
                ['iconUrl' => 'images/share/max.svg', 'title' => 'MAX', 'shareUrl' => 'https://max.ru/:share?text={url}'],
            ],
        ], new StubHasBlockSections, 0);

        // `@js()` escapes slashes, so assert on the slash-free tail of each template
        $this->assertStringContainsString('share.php?url={url}', $html);
        $this->assertStringContainsString(':share?text={url}', $html);
        $this->assertStringContainsString('aria-label="ВКонтакте"', $html);
        $this->assertStringContainsString('aria-label="MAX"', $html);
    }

    public function test_share_targets_are_templates_filled_in_by_the_browser(): void
    {
        $html = (new ShareButtonRenderer)->render([
            'socials' => [
                ['iconUrl' => 'images/share/vk.svg', 'title' => 'ВКонтакте', 'shareUrl' => 'https://vk.com/share.php?url={url}&title={title}'],
            ],
        ], new StubHasBlockSections, 0);

        $this->assertStringContainsString("replaceAll('{url}', encodeURIComponent(location.href))", $html);
        $this->assertStringContainsString("replaceAll('{title}', encodeURIComponent(document.title))", $html);
    }

    public function test_share_bar_is_dropped_when_no_socials_are_configured(): void
    {
        $html = (new ShareButtonRenderer)->render([
            'btnLabel' => 'Read more',
        ], new StubHasBlockSections, 0);

        $this->assertStringNotContainsString('aria-expanded', $html);
        $this->assertStringNotContainsString('navigator.clipboard', $html);
        $this->assertStringContainsString('Read more', $html);
    }

    public function test_copy_link_button_follows_the_toggle(): void
    {
        $socials = [
            ['iconUrl' => 'images/share/vk.svg', 'title' => 'ВКонтакте', 'shareUrl' => 'https://vk.com/share.php?url={url}'],
        ];

        $with = (new ShareButtonRenderer)->render([
            'socials' => $socials,
            'showCopy' => true,
        ], new StubHasBlockSections, 0);

        $without = (new ShareButtonRenderer)->render([
            'socials' => $socials,
            'showCopy' => false,
        ], new StubHasBlockSections, 0);

        $this->assertStringContainsString('@click="copy()"', $with);
        $this->assertStringNotContainsString('@click="copy()"', $without);
    }
}
