<?php

namespace App\Filament\Resources\GlobalSettings\Pages;

use App\Filament\Resources\GlobalSettings\GlobalSettingResource;
use App\Models\GlobalSetting;
use Filament\Resources\Pages\EditRecord;

class EditGlobalSetting extends EditRecord
{
    protected static string $resource = GlobalSettingResource::class;
    
    public function mount($record = null): void
    {
        // если записи нет — создаём пустую
        $setting = GlobalSetting::query()->first() ?? GlobalSetting::create([]);
        
        $record = $setting->getKey();
        
        parent::mount($record);
    }
    
    protected function getHeaderActions(): array
    {
        return [];
    }
}
