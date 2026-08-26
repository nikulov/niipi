<?php

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;

final class Anchor
{
    public static function key(): string
    {
        return 'anchor';
    }

    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.anchor'))
            ->icon('heroicon-o-link')
            ->columnSpanFull()
            ->schema([
                TextInput::make('anchor')->label(__('panel.anchor'))
                    ->prefix('#')
                    ->placeholder('my-section')
                    ->trim()
                    ->maxLength(100)
                    ->alphaDash()
                    ->live(onBlur: true)
                    ->rule(function ($livewire) {
                        return function (string $attribute, mixed $value, Closure $fail) use ($livewire) {
                            if (blank($value)) {
                                return;
                            }

                            $counts = self::collectAnchorCounts($livewire->data ?? []);

                            if (($counts[$value] ?? 0) > 1) {
                                $fail(__('panel.anchor_duplicate', ['anchor' => $value]));
                            }
                        };
                    }),
            ]);
    }

    /**
     * @return array<string, int>
     */
    private static function collectAnchorCounts(mixed $state): array
    {
        $counts = [];
        self::walkAnchors($state, $counts);

        return $counts;
    }

    /**
     * @param  array<string, int>  $counts
     */
    private static function walkAnchors(mixed $state, array &$counts): void
    {
        if (! is_array($state)) {
            return;
        }

        foreach ($state as $value) {
            if (! is_array($value)) {
                continue;
            }

            if (($value['type'] ?? null) === self::key()) {
                $anchor = $value['data']['anchor'] ?? null;

                if (is_string($anchor) && filled($anchor)) {
                    $counts[$anchor] = ($counts[$anchor] ?? 0) + 1;
                }
            }

            self::walkAnchors($value, $counts);
        }
    }
}
