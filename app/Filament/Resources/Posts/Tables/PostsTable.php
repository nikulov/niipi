<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Post;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label(__('panel.title'))
                    ->limit(150)
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('status')->label(__('panel.status'))
                    ->badge()
                    ->icon(fn (PostStatus $state) => $state->getIcon()),
                TextColumn::make('categories.name')->label(__('panel.category'))
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
                        if (!$state) {
                            return null;
                        }
                        $date = Carbon::parse($state);
                        $hours = $date->diffInHours(now());
                        if ($hours > 6) {
                            return $date->translatedFormat('d.m.Y H:i');
                        }
                        return $date->diffForHumans();
                    }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label(__('panel.status'))
                    ->columnSpan(3)
                    ->options(PostStatus::class)
                    ->multiple()
                    ->preload(),
                SelectFilter::make('category_id')->label(__('panel.category'))
                    ->columnSpan(3)
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')->label(__('panel.created_from'))
                            ->columnSpan(3)
                            ->maxDate(now())
                            ->minDate(fn() => Post::min('created_at')),
                        DatePicker::make('created_until')->label(__('panel.created_until'))
                            ->columnSpan(3)
                            ->maxDate(now()),
                    ])
                    ->columns(6)
                    ->columnSpan(6)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            
            ], layout: FiltersLayout::AboveContent)->deferFilters(false)
            ->filtersFormColumns(12)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label(__('panel.filter')),
            )
            ->recordActions([
                EditAction::make()->label(__(''))
                    ->iconSize('md')
                    ->tooltip(__('panel.edit_post')),
                DeleteAction::make()->label(__(''))
                    ->iconSize('md')
                    ->tooltip(__('panel.delete_post')),
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
