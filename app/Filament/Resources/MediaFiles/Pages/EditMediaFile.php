<?php

namespace App\Filament\Resources\MediaFiles\Pages;

use App\Enums\MediaFileType;
use App\Filament\Resources\MediaFiles\MediaFileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditMediaFile extends EditRecord
{
    protected static string $resource = MediaFileResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $path = $data['path'] ?? null;

        if ($path && Storage::disk('public')->exists($path)) {
            $data['filename'] = basename($path);
            $data['mime_type'] = Storage::disk('public')->mimeType($path);
            $data['size'] = Storage::disk('public')->size($path);
            $data['type'] = MediaFileType::fromMimeType($data['mime_type'])->value;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription(function () {
                    $record = $this->getRecord();
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
                ->before(function (): void {
                    $record = $this->getRecord();
                    if ($record->existsOnDisk()) {
                        Storage::disk($record->disk)->delete($record->path);
                    }
                }),
        ];
    }
}
