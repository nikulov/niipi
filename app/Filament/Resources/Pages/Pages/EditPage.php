<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->action(fn () => $this->save()),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Страница успешно сохранена') // свой текст
            ->icon('heroicon-o-check-circle')     // иконка
            ->iconColor('success')                // цвет иконки
            ->body('Все изменения применены и сохранены.'); // дополнительный текст
    }
}