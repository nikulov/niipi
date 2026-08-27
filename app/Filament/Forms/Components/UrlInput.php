<?php

namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Text;
use Illuminate\Support\Str;

class UrlInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->live()
            ->trim()
            ->prefix('niipigrad.ru/')
            ->belowContent(self::slashHint())
            ->suffixActions([
                Action::make('open_page')
                    ->icon('heroicon-o-globe-alt')
                    ->hiddenLabel()
                    ->color('success')
                    ->url(fn ($state) => self::normalize($state))
                    ->openUrlInNewTab()
                    ->tooltip(__('Open page in new tab'))
                    ->extraAttributes(['class' => 'text-green-500 [&>svg]:text-green-500']),
            ])
            ->maxLength(255);
    }

    /**
     * Short warning under the field; the full explanation hangs in its tooltip.
     *
     * Built here rather than through `helperText()` so the icon and the tooltip
     * sit next to the text instead of up by the label. The icon is a separate
     * component on purpose: `Text::icon()` only renders in the badge branch of
     * `filament-schemas::components.text`, so on a plain line it is dropped.
     * `belowContent` lays its children out inline, so the two sit in one row.
     *
     * @return array{Text, Icon}
     */
    public static function slashHint(): array
    {
        $tooltip = __('panel.url_input_tooltip');

        return [
            Text::make(__('panel.url_input_hint')),
            Icon::make('heroicon-m-information-circle')
                ->color('gray')
                ->tooltip($tooltip),
        ];
    }

    protected static function normalize(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (Str::contains($value, '.') && ! Str::startsWith($value, ['/'])) {
            return 'https://'.$value;
        }

        $value = '/'.ltrim($value, '/');

        return url($value);
    }
}
