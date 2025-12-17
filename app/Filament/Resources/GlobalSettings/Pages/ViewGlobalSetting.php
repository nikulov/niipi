<?php

namespace App\Filament\Resources\GlobalSettings\Pages;

use App\Filament\Resources\GlobalSettings\GlobalSettingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGlobalSetting extends ViewRecord
{
    protected static string $resource = GlobalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
