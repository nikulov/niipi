<?php

namespace App\View\Components\Menu;

use App\Models\Menu;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DesktopFooter extends Component
{
    public array $menuItems;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->menuItems = Menu::getFooterMenuItems() ?? [];
    }
    
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.menu.desktop-footer');
    }
}
