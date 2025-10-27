<?php

namespace App\Filament\Resources\Posts\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label(__('panel.title'))
                    ->limit(50)
                    ->sortable(),
                TextColumn::make('status')->label(__('panel.status'))
                    ->badge(),
                TextColumn::make('published_at')->label(__('panel.published_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('panel.updated_at'))
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (! $state) {return null;}
                        $date = Carbon::parse($state);
                        $hours = $date->diffInHours(now());
                        if ($hours > 6) {
                            return $date->translatedFormat('d.m.Y H:i');
                        }
                        return $date->diffForHumans();
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->searchable(['title'])
            ->searchPlaceholder(__('panel.search_placeholder'));
    }
}
