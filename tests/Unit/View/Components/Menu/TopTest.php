<?php

namespace Tests\Unit\View\Components\Menu;

use App\View\Components\Menu\Top;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopTest extends TestCase
{
    use RefreshDatabase;

    public function test_populates_menu_items_from_model_helper(): void
    {
        $component = new Top();

        $this->assertIsArray($component->menuItems);
    }

    public function test_render_returns_top_view(): void
    {
        $component = new Top();
        $view = $component->render();

        $this->assertSame('components.menu.top', $view->name());
    }
}
