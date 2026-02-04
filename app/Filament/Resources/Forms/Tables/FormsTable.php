<?php

namespace App\Filament\Resources\Forms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query
                    ->with(['fields' => fn ($q) => $q->orderBy('sort')])
                    ->withCount('submissions');
            })
            ->columns([
                
                TextColumn::make('id')->label(__('panel.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                
                TextColumn::make('name')->label(__('panel.name'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('fields_list')->label(__('panel.fields'))
                    ->state(function ($record) {
                        return $record->fields
                            ->where('is_enabled', true)
                            ->sortBy('sort')
                            ->pluck('type')
                            ->values()
                            ->all();
                    })
                    ->badge()
                    ->color('green')
                    ->wrap()
                    ->separator(' ')
                    ->toggleable(),
                
                TextColumn::make('submissions_count')->label(__('panel.submissions_count'))
                    ->url(fn ($record) => route('filament.admin.resources.form-submissions.index', [
                        'filters' => [
                            'form' => [
                                'name' => [
                                    'values' => [
                                        $record->id,
                                    ],
                                ],
                            ],
                        ],
                    ]))
                    ->sortable()
                    ->icon(Heroicon::ArrowTopRightOnSquare)
                    ->iconPosition(IconPosition::After)
                    ->alignCenter(),
                
                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
