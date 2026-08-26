<?php

namespace App\Filament\Forms\Components;

use App\Models\MediaFile;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class MediaPickerAction
{
    public static function make(
        string $fieldName,
        bool $imagesOnly = false,
        bool $multiple = false,
        ?array $acceptedMimeTypes = null,
        ?int $maxSize = null,
    ): Closure {
        return fn (): Action => Action::make('media_picker_'.$fieldName)
            ->label(__('panel.media_choose_from_library'))
            ->icon('heroicon-o-folder-open')
            ->color('info')
            ->modalHeading(__('panel.media_picker_title'))
            ->modalWidth('7xl')
            ->schema([
                TextInput::make('media_search')
                    ->hiddenLabel()
                    ->placeholder(__('panel.search'))
                    ->prefixIcon('heroicon-o-magnifying-glass')
                    ->live(debounce: 500)
                    ->dehydrated(false)
                    ->afterStateUpdated(fn (Set $set) => $set('media_page', 1)),

                Hidden::make('media_page')->default(1)->live()->dehydrated(false),

                MediaGrid::make('media_file_ids')
                    ->hiddenLabel()
                    ->imagesOnly($imagesOnly)
                    ->acceptedMimeTypes($acceptedMimeTypes)
                    ->multiple($multiple)
                    ->maxSize($maxSize),
            ])
            ->action(function (array $data, Get $get, Set $set) use ($fieldName, $multiple): void {
                $selected = $data['media_file_ids'] ?? null;

                $ids = $multiple
                    ? (is_array($selected) ? $selected : [$selected])
                    : ($selected ? [$selected] : []);

                $ids = array_filter($ids);
                if (empty($ids)) {
                    return;
                }

                $paths = MediaFile::whereIn('id', $ids)->pluck('path')->toArray();
                if (empty($paths)) {
                    return;
                }

                if ($multiple) {
                    $existing = $get($fieldName) ?? [];
                    if (is_array($existing)) {
                        $paths = array_unique(array_merge(array_values($existing), $paths));
                    }
                    $set($fieldName, $paths);
                } else {
                    $set($fieldName, $paths[0]);
                }
            });
    }
}
