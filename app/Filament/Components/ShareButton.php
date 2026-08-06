<?php

namespace App\Filament\Components;

use App\Filament\Forms\Components\UrlInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

final class ShareButton
{
    public static function key(): string
    {
        return 'share-button';
    }

    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.share_btn'))
            ->columnSpanFull()
            ->schema([

                TextInput::make('btnLabel')->label(__(key: 'panel.btn_label'))
                    ->required()
                    ->columnSpan(12),

                UrlInput::make('btnUrl')->label(__(key: 'panel.btn_url'))
                    ->columnSpan(12)
                    ->required(),

                Select::make('btnType')->label(__(key: 'panel.type'))
                    ->options([
                        'btn-primary' => __('panel.primary'),
                        'btn-secondary' => __('panel.secondary'),
                        'btn-accent' => __('panel.accent'),
                        'btn-transparent' => __('panel.accent_additional'),
                    ])
                    ->required()
                    ->columnSpan(8),

                Select::make('btnPosition')->label(__(key: 'panel.position'))
                    ->options([
                        'start' => __('panel.left'),
                        'center' => __('panel.center'),
                        'end' => __('panel.right'),
                    ])
                    ->required()
                    ->columnSpan(8),

                Toggle::make('blank')->label(__(key: 'panel.open_page_in_new_tab'))
                    ->inline(false)
                    ->default(false)
                    ->columnSpan(6),

            ])->columns(24);
    }

    /** Default block for Post/Project: share bar plus a button back to the section index. */
    public static function getDefaultBlock(string $btnUrl, string $btnLabel): array
    {
        return [
            [
                'type' => self::key(),
                'data' => [
                    'btnLabel' => $btnLabel,
                    'btnUrl' => $btnUrl,
                    'btnType' => 'btn-primary',
                    'btnPosition' => 'end',
                    'blank' => false,
                ],
            ],
        ];
    }
}
