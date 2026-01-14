<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('panel.save'))
                ->action(fn() => $this->save()),
            DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function afterSave(): void
    {
        cache()->tags(['projects', 'categories'])->flush();
    }
}
