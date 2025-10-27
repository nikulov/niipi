<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save'))
                ->action(fn() => $this->save()),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__( 'Category saved successfully' ))
            ->icon('heroicon-o-check-circle')
            ->iconColor('success');
    }
}
