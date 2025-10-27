<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__( 'panel.post_created_successfully' ))
            ->icon('heroicon-o-check-circle')
            ->iconColor('success');
    }
}
