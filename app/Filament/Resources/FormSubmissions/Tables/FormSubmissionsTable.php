<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('panel.id'))
                    ->sortable(),
                
                TextColumn::make('form.name')->label(__('panel.form'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('status')->label(__('panel.status'))
                    ->badge()
                    ->sortable(),
                
                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                
                TextColumn::make('ip')->label(__('panel.ip'))
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
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
