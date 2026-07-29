<?php

namespace Tests\Unit\Filament\Components\BlockRegistry;

use App\Filament\Components\BlockRegistry\BlockRegistry;
use Filament\Forms\Components\Builder\Block;
use Tests\TestCase;

class BlockRegistryTest extends TestCase
{
    public function test_all_returns_full_catalog(): void
    {
        $names = $this->names(BlockRegistry::all());

        $expected = [
            'accordion',
            'accordion-light',
            'anchor',
            'bg-for-main-section',
            'button',
            'cards-block-with-button',
            'cards-block-with-image-title',
            'category-list',
            'form',
            'gallery',
            'image-full',
            'image-text',
            'image-tittle-full-width',
            'info-block-with-achievements',
            'info-block-with-buttons',
            'modal-block',
            'news-block',
            'news-full',
            'projects-block',
            'projects-full',
            'slider-full-width',
            'text-full',
            'title',
            'yandex-map',
        ];

        sort($names);
        sort($expected);
        $this->assertSame($expected, $names);
    }

    public function test_top_section_is_anchor_hero_and_slider(): void
    {
        $this->assertSame(
            ['anchor', 'image-tittle-full-width', 'slider-full-width'],
            $this->names(BlockRegistry::topSection())
        );
    }

    public function test_bottom_section_is_anchor_news_and_projects(): void
    {
        $this->assertSame(
            ['anchor', 'news-block', 'projects-block'],
            $this->names(BlockRegistry::bottomSection())
        );
    }

    public function test_main_section_excludes_top_bottom_only_and_includes_tabs(): void
    {
        $names = $this->names(BlockRegistry::mainSection());

        $this->assertContains('tabs-block', $names);
        $this->assertContains('bg-for-main-section', $names);
        $this->assertNotContains('image-tittle-full-width', $names);
        $this->assertNotContains('slider-full-width', $names);
    }

    public function test_tabs_and_modal_exclude_their_own_container_block(): void
    {
        $this->assertNotContains('tabs-block', $this->names(BlockRegistry::tabs()));
        $this->assertNotContains('modal-block', $this->names(BlockRegistry::modal()));

        $this->assertContains('tabs-block', $this->names(BlockRegistry::modal()));
    }

    /**
     * @param  array<int, Block>  $blocks
     * @return array<int, string>
     */
    private function names(array $blocks): array
    {
        return array_map(fn (Block $b) => $b->getName(), $blocks);
    }
}
