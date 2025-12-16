<?php


namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
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
            ->suffixActions([
                Action::make('open_page')
                    ->icon('heroicon-o-globe-alt')
                    ->hiddenLabel()
                    ->color('success')
                    ->url(fn($state) => self::normalize($state))
                    ->openUrlInNewTab()
                    ->tooltip(__('Open page in new tab'))
                    ->extraAttributes(['class' => 'text-green-500 [&>svg]:text-green-500']),
            ])
            ->maxLength(255);
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
        
        if (Str::contains($value, '.') && !Str::startsWith($value, ['/'])) {
            return 'https://' . $value;
        }
        
        $value = '/' . ltrim($value, '/');
        
        return url($value);
    }
}