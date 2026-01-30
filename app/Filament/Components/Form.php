<?php


namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;

final class Form
{
    public static function key(): string
    {
        return 'form';
    }
    
    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.form'))
            ->columns(24)
            ->columnSpanFull()
            ->schema([
                
                Select::make('form_id')->label(__('panel.form'))
                    ->options(\App\Models\Form::query()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->columnSpan(8)
                    ->required(),
                
//                Select::make('layout')->label(__('panel.layout'))
//                    ->columnSpan(8)
//                    ->options([
//                        'inline' => __('panel.inline'),
//                        'modal' => __('panel.modal'),
//                    ])
//                    ->default('modal')
//                    ->required(),
            ]);
    }
}
