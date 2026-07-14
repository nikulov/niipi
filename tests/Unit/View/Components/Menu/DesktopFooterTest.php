<?php

namespace Tests\Unit\View\Components\Menu;

use App\View\Components\Menu\DesktopFooter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesktopFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_populates_menu_items_from_model_helper(): void
    {
        $component = new DesktopFooter();

        $this->assertIsArray($component->menuItems);
    }

    public function test_render_returns_desktop_footer_view(): void
    {
        $component = new DesktopFooter();
        $view = $component->render();

        $this->assertSame('components.menu.desktop-footer', $view->name());
    }
}
