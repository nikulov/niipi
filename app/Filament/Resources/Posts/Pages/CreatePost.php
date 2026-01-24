<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Components\CategoryList;
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
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['main_section'] = $this->appendDefaultMainBlock($data['main_section'] ?? []);
        
        return $data;
    }
    
    
    private function appendDefaultMainBlock(array $state): array
    {
        if (empty($state)) {
            return $state;
        }
        
        return [
            ...$state,
            ...CategoryList::getDefaultBlock(),
        ];
    }
    
    protected function afterSave(): void
    {
        cache()->tags(['news', 'categories'])->flush();
    }
}
