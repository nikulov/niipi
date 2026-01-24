<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('panel.save'))
                ->action(fn() => $this->save()),
            DeleteAction::make(),
        ];
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('panel.post_saved_successfully'))
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                Action::make('view')->label(__('panel.view_post'))
                    ->url(route('filament.admin.resources.posts.view', ['record' => $this->record]))
                    ->openUrlInNewTab(),
            ]);
    }
    
    
    protected function afterSave(): void
    {
        cache()->tags(['news', 'categories'])->flush();
    }
}
