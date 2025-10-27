<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label(__('panel.save'))
                ->action(fn() => $this->save()),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
        ->title(__( 'panel.post_saved_successfully' ))
        ->icon('heroicon-o-check-circle')
        ->iconColor('success');
    }
}
