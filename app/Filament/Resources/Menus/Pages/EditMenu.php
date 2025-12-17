<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use App\Models\GlobalSetting;
use App\Models\Menu;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;
    
    
    public function mount($record = null): void
    {
        $menu = Menu::query()->first() ?? Menu::create([]);
        
        $record = $menu->getKey();
        
        parent::mount($record);
    }
    
    protected function getHeaderActions(): array
    {
        return [];
    }
    
}
