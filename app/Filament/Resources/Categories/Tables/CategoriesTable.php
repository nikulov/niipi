<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('panel.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')->label(__('panel.status'))
                    ->sortable()
                    ->searchable()
                    ->badge(),
                TextColumn::make('type')->label(__('panel.type'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('posts_count')->label(__('panel.posts_count'))
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('slug')->label(__('panel.slug'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label(__(''))
                    ->iconSize('md')
                    ->tooltip(__('panel.edit_category')),
                DeleteAction::make()->label(__(''))
                    ->iconSize('md')
                    ->tooltip(__('panel.delete_category')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
