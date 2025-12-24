<?php

namespace App\View\Components\Menu;

use AllowDynamicProperties;
use App\Models\Menu;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

#[AllowDynamicProperties]
class Top extends Component
{
    public array $menuItems;
    public function __construct()
    {
        $this->menuItems = Menu::getTopMenuItems() ?? [];
    }
    
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.menu.top');
    }
}
