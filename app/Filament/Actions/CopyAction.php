<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class CopyAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'copy';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('')
            ->icon(Heroicon::DocumentDuplicate)
            ->iconSize('md')
            ->color('gray')
            ->requiresConfirmation()
            ->modalSubmitActionLabel(__('panel.copy'))
            ->authorize(fn (Model $record): bool => Gate::allows('create', $record::class))
            ->action(fn (Model $record) => $record->duplicate());
    }
}
