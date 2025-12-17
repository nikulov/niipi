<?php

namespace App\Filament\Resources\GlobalSettings\Pages;

use App\Filament\Resources\GlobalSettings\GlobalSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGlobalSettings extends ListRecords
{
    protected static string $resource = GlobalSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
