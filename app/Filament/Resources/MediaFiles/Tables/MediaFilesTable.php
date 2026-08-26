<?php

namespace App\Filament\Resources\MediaFiles\Tables;

use App\Enums\MediaFileType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class MediaFilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('path')->label(__('panel.thumbnail'))
                    ->disk('public')->width(60)->imageHeight(60)->square()
                    ->getStateUsing(fn ($record) => $record->type === MediaFileType::Image ? $record->path : null),

                TextColumn::make('filename')->label(__('panel.file_name'))
                    ->searchable()->sortable()->limit(40)
                    ->tooltip(fn ($record) => $record->path),

                TextColumn::make('type')->label(__('panel.type'))->badge()->sortable(),

                TextColumn::make('human_size')->label(__('panel.size'))
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('size', $direction)),

                TextColumn::make('usages_count')->label(__('panel.media_usages_count'))
                    ->counts('usages')->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('title')->label(__('panel.title'))
                    ->searchable()->limit(30)->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mime_type')->label(__('panel.mime_type'))
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->label(__('panel.type'))
                    ->options(MediaFileType::class)->multiple(),

                TernaryFilter::make('has_usages')->label(__('panel.media_used'))
                    ->queries(
                        true: fn ($query) => $query->has('usages'),
                        false: fn ($query) => $query->doesntHave('usages'),
                    ),
            ], layout: FiltersLayout::AboveContent)->deferFilters(false)
            ->recordActions([
                EditAction::make()->label('')->iconSize('md')->tooltip(__('panel.edit')),

                Action::make('copy_url')->label('')
                    ->icon('heroicon-o-clipboard')->iconSize('md')
                    ->tooltip(__('panel.media_copy_url'))->color('info')
                    ->url(fn ($record) => $record->url)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click.prevent' => "window.navigator.clipboard.writeText('".addslashes($record->url)."'); \$tooltip('".addslashes(__('panel.media_url_copied'))."')",
                    ]),

                DeleteAction::make()->label('')->iconSize('md')->tooltip(__('panel.delete'))
                    ->modalDescription(function ($record) {
                        $usages = $record->usages()->with('usable')->get();
                        if ($usages->isEmpty()) {
                            return null;
                        }

                        $list = $usages->map(function ($usage) {
                            $model = $usage->usable;
                            if (! $model) {
                                return null;
                            }
                            $type = class_basename($model);
                            $name = $model->title ?? $model->name ?? $model->full_name ?? $model->author ?? "#{$model->getKey()}";

                            return "- {$type}: {$name} ({$usage->field})";
                        })->filter()->join("\n");

                        return __('panel.media_confirm_delete_used')."\n\n".$list;
                    })
                    ->before(function ($record): void {
                        if ($record->existsOnDisk()) {
                            Storage::disk($record->disk)->delete($record->path);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records): void {
                            foreach ($records as $record) {
                                if ($record->existsOnDisk()) {
                                    Storage::disk($record->disk)->delete($record->path);
                                }
                            }
                        }),
                ]),
            ]);
    }
}
