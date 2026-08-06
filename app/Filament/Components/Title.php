<?php

namespace App\Filament\Components;

use App\Models\Post;
use App\Models\Project;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

final class Title
{
    public static function key(): string
    {
        return 'title';
    }

    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.title_label'))
            ->columnSpanFull()
            ->schema([

                Textarea::make('title')->label(__('panel.heading'))
                    ->autosize()
                    ->columnSpanFull()
                    ->trim()
                    ->default(fn ($livewire) => self::defaultHeading($livewire))
                    ->required(),

                Select::make('type')->label(__('panel.heading_size'))
                    ->options([
                        'h2' => __('panel.heading').' 2',
                        'h3' => __('panel.heading').' 3',
                    ])
                    ->required()
                    ->columnSpan(6),

                Select::make('position')->label(__('panel.position_title'))
                    ->default('left')
                    ->options([
                        'left' => __('panel.left'),
                        'center' => __('panel.center'),
                        'right' => __('panel.right'),
                    ])
                    ->required()
                    ->columnSpan(6),

            ])->columns(12);
    }

    /** Default main-section block: the record title lands here on create. */
    public static function getDefaultBlock(): array
    {
        return [
            [
                'type' => self::key(),
                'data' => [
                    'type' => 'h2',
                    'position' => 'center',
                ],
            ],
        ];
    }

    /** Copy the record title into the first main-section title block, unless it was edited by hand. */
    public static function syncRecordTitle(Set $set, Get $get, ?string $state, ?string $old): void
    {
        $key = self::findSyncableBlockKey($get('main_section'), $old);

        if ($key === null) {
            return;
        }

        $set("main_section.{$key}.data.title", (string) $state);
    }

    /**
     * Key of the first title block that still mirrors the record title, or null when there is none.
     */
    private static function findSyncableBlockKey(mixed $items, ?string $old): string|int|null
    {
        if (! is_array($items)) {
            return null;
        }

        foreach ($items as $key => $item) {
            if (! is_array($item) || ($item['type'] ?? null) !== self::key()) {
                continue;
            }

            $heading = $item['data']['title'] ?? null;

            if (filled($heading) && $heading !== $old) {
                return null;
            }

            return $key;
        }

        return null;
    }

    /** Prefill a manually added block, as long as it is the only title block of a post or project. */
    private static function defaultHeading(mixed $livewire): ?string
    {
        if (! is_object($livewire) || ! self::isPostOrProject($livewire)) {
            return null;
        }

        $data = $livewire->data ?? [];
        $recordTitle = is_array($data) ? ($data['title'] ?? null) : null;

        if (! is_string($recordTitle) || blank($recordTitle)) {
            return null;
        }

        return self::countBlocks($data) > 1 ? null : $recordTitle;
    }

    /** Count title blocks across every section, including nested tabs and modals. */
    private static function countBlocks(mixed $state): int
    {
        if (! is_array($state)) {
            return 0;
        }

        $count = 0;

        foreach ($state as $value) {
            if (! is_array($value)) {
                continue;
            }

            if (($value['type'] ?? null) === self::key()) {
                $count++;
            }

            $count += self::countBlocks($value);
        }

        return $count;
    }

    private static function isPostOrProject(object $livewire): bool
    {
        $modelClass = null;

        if (method_exists($livewire, 'getRecord')) {
            $record = $livewire->getRecord();

            if ($record) {
                $modelClass = $record::class;
            }
        }

        if ($modelClass === null && method_exists($livewire, 'getResource')) {
            $resource = $livewire::getResource();

            if (is_string($resource) && method_exists($resource, 'getModel')) {
                $modelClass = $resource::getModel();
            }
        }

        return in_array($modelClass, [Post::class, Project::class], true);
    }
}
