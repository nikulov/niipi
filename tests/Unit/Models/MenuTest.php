<?php

namespace Tests\Unit\Models;

use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_menu_items_are_normalized(): void
    {
        $menu = Menu::query()->firstOrFail();

        $menu->update([
            'top_items' => [
                [
                    'type' => 'custom',
                    'label' => 'External',
                    'url' => 'https://example.com',
                    'blank' => true,
                ],
                [
                    'type' => 'custom',
                    'label' => 'Internal',
                    'url' => 'news',
                ],
                [
                    'type' => 'page',
                    'label' => 'Home',
                    'page_slug' => 'home',
                ],
                [
                    'type' => 'page',
                    'label' => 'About',
                    'page_slug' => '/about',
                    'children' => [
                        [
                            'type' => 'page',
                            'label' => 'Team',
                            'page_slug' => 'team',
                        ],
                    ],
                ],
            ],
        ]);

        $items = Menu::getTopMenuItems();

        $this->assertSame('https://example.com', $items[0]['href']);
        $this->assertSame('/news', $items[1]['href']);
        $this->assertSame('/', $items[2]['href']);
        $this->assertSame('/about', $items[3]['href']);
        $this->assertSame('/team', $items[3]['children'][0]['href']);
        $this->assertTrue($items[0]['blank']);
    }

    public function test_footer_menu_items_are_normalized(): void
    {
        $menu = Menu::query()->firstOrFail();

        $menu->update([
            'footer_items' => [
                [
                    'type' => 'custom',
                    'label' => 'Docs',
                    'url' => 'docs',
                ],
            ],
        ]);

        $items = Menu::getFooterMenuItems();

        $this->assertSame('/docs', $items[0]['href']);
    }
}
