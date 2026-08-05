<?php

namespace App\Filament\Components;

use App\Filament\Forms\Components\MediaPickerAction;
use App\Filament\Forms\Components\UrlInput;
use App\Models\Project;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

final class ProjectsBlock
{
    public static function key(): string
    {
        return 'projects-block';
    }

    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.projects-block'))
            ->columnSpanFull()
            ->columns(12)
            ->schema([

                Textarea::make('title')->label(__(key: 'panel.title'))
                    ->columnSpan(3)
                    ->autosize()
                    ->default(__(key: 'panel.projects'))
                    ->required(),

                TextInput::make('limit')->label(__(key: 'panel.quantity'))
                    ->columnSpan(1)
                    ->numeric()
                    ->minValue(1)
                    ->default(4)
                    ->required(),

                TextInput::make('btnLabel')->label(__(key: 'panel.btn_label'))
                    ->columnSpan(3)
                    ->default(__(key: 'panel.all-projects'))
                    ->required(),

                UrlInput::make('btnUrl')->label(__(key: 'panel.btn_url'))
                    ->columnSpan(5)
                    ->required()
                    ->default('projects'),

                Select::make('projectIds')->label(__(key: 'panel.pinned_projects'))
                    ->columnSpan(12)
                    ->multiple()
                    ->searchable()
                    ->options(fn () => Project::query()->orderBy('title')->pluck('title', 'id'))
                    ->helperText(__(key: 'panel.pinned_projects_hint')),

                FileUpload::make('bgImageUrl')->label(__(key: 'panel.bg_image'))
                    ->columnSpan(12)
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable()
                    ->disk('public')
                    ->directory('images')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatioOptions([null, '16:9'])
                    ->required()
                    ->hintAction(MediaPickerAction::make('bgImageUrl', imagesOnly: true)),
            ]);
    }
}
