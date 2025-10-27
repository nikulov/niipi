<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;
    
    public function getTitle(): string
    {
        return __('panel.view_post');
    }
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
