<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__( 'panel.category_created_successfully' ))
            ->icon('heroicon-o-check-circle')
            ->iconColor('success');
    }
}
