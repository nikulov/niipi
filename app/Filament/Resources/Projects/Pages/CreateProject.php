<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Components\CategoryList;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['main_section'] = $this->appendDefaultMainBlock($data['main_section'] ?? []);
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
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
        cache()->tags(['projects', 'categories'])->flush();
    }
}
