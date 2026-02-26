<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('name')->label(__('panel.name'))
                    ->searchable(),
                
                TextColumn::make('role')->label(__('panel.role'))
                    ->badge(),
                
                TextColumn::make('email')->label(__('panel.email'))
                    ->searchable(),
                
                IconColumn::make('email_verified_at')->label(__('panel.email_verified_at'))
                    ->getStateUsing(fn ($record): bool => filled($record->email_verified_at))
                    ->boolean()
                    ->tooltip(function ($record): string {
                        if (filled($record->email_verified_at)) {
                            return $record->email_verified_at->format('H:i:s d.m.Y');
                        }

                        return __('panel.email_not_verified');
                    })
                    ->sortable(),
                
                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')->label(__('panel.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
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
