<?php

namespace App\Filament\Resources\MediaFiles\Schemas;

use App\Models\MediaFile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Fieldset::make('upload')->label(__('panel.media_upload'))
                ->columns(24)->columnSpanFull()
                ->schema([
                    FileUpload::make('path')->label(__('panel.file'))
                        ->columnSpanFull()->required()
                        ->downloadable()->openable()
                        ->getUploadedFileNameForStorageUsing(
                            fn (TemporaryUploadedFile $file): string => generate_uploaded_file_name($file)
                        )
                        ->moveFiles()->disk('public')->directory('media')->visibility('public')
                        ->maxSize(10240)
                        ->acceptedFileTypes([
                            'image/*',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/plain',
                        ]),
                ]),

            Fieldset::make('meta')->label(__('panel.settings'))
                ->columns(24)->columnSpanFull()
                ->schema([
                    TextInput::make('title')->label(__('panel.title'))->columnSpan(12)->maxLength(255),
                    TextInput::make('alt')->label(__('panel.alt'))->columnSpan(12)->maxLength(255),
                ]),

            Section::make(__('panel.media_file_info'))
                ->collapsible()
                ->collapsed(fn (?MediaFile $record) => $record === null)
                ->hidden(fn (?MediaFile $record) => $record === null)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('url')->label(__('panel.url'))->copyable()->columnSpanFull(),
                    TextEntry::make('filename')->label(__('panel.file_name')),
                    TextEntry::make('mime_type')->label(__('panel.mime_type')),
                    TextEntry::make('human_size')->label(__('panel.size')),
                    TextEntry::make('type')->label(__('panel.type'))->badge(),
                    TextEntry::make('usages_list')->label(__('panel.media_used_in'))
                        ->columnSpanFull()
                        ->getStateUsing(function (MediaFile $record): string {
                            $usages = $record->usages()->with('usable')->get();
                            if ($usages->isEmpty()) {
                                return __('panel.media_not_used');
                            }

                            return $usages->map(function ($usage) {
                                $model = $usage->usable;
                                if (! $model) {
                                    return null;
                                }
                                $type = class_basename($model);
                                $name = $model->title ?? $model->name ?? $model->full_name ?? $model->author ?? "#{$model->getKey()}";

                                return "{$type}: {$name} ({$usage->field})";
                            })->filter()->join("\n");
                        })
                        ->markdown(),
                ]),
        ]);
    }
}
