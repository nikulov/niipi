# Оригинальный промт (справка)

Исходный текст промта на внедрение медиа-менеджера, полученный
2026-07-28. Живёт рядом с планом, чтобы будущие сессии имели полный
контекст с кодом всех классов.

**Отклонения от промта под наш проект** — см. в README и в каждом
`NN-*.md`:
- `MediaFile::booted()` без `cache()->tags(['content'])->flush()` — тега нет.
- `MediaFileResource` использует наш `RoleAccessResource` с
  `[Admin, Editor, Viewer]` и `getNavigationGroup() = 'Медиа'`.
- В `AdminPanelProvider` добавляется группа `'Медиа'`.
- Хелпер `generate_uploaded_file_name` — в существующий
  `app/helpers.php` (уже подключён в `bootstrap/app.php`), без правки
  `composer.json`.
- Livewire namespace для `TemporaryUploadedFile` — под Livewire 3.7
  (совпадает с промтом).
- `BaseListRecords` не используется — стандартный `ListRecords`.

---

# Промт: перенос медиа-менеджера в другой Laravel/Filament проект

Скопируй всё, что ниже, в другой проект и попроси Claude реализовать.
Совместимость: **Laravel 12 + Filament 5 + Livewire 4 + Alpine 3 + Tailwind 4**.

*(Примечание: код промта использует Filament 4 / Livewire 3 API, что
совпадает с нашим стеком.)*

---

## Задача

Реализуй встроенный медиа-менеджер: каталог загруженных файлов с
отслеживанием, где каждый файл используется (модель + поле), с админ-CRUD
на Filament и модальным пикером для `FileUpload`.

Ключевые свойства:

1. Регистр всех файлов диска `public` в таблице `media_files` (path, mime,
   size, type, title, alt, uploaded_by).
2. Автоматический трекинг использований: пивот `media_file_usages` с
   `morphs('usable')` + `field`. Синк на `saved`/`deleted` модели.
3. **Без списков полей.** Сервис сам сканирует все атрибуты модели
   (строки + JSON) и находит пути к файлам по расширению + существованию
   на диске. Единственное условие — на модели есть трейт-маркер
   `TracksMediaUsage`.
4. Filament-ресурс со списком, фильтрами (тип / used–unused), превью,
   копированием URL, показом «где используется», конфирмом удаления.
5. Модальный медиа-пикер `MediaPickerAction::make('fieldName', imagesOnly, multiple, acceptedMimeTypes, maxSize)`
   как `hintAction` на `FileUpload`. Поиск + пагинация + грид на Alpine.
6. Артизан-команда `media:sync` для первичной индексации существующего
   диска и пересборки usages.

---

## Архитектура (data flow)

```
[FileUpload / Livewire] --> storage/app/public/media/*.ext
                                     |
                              saved() событие
                                     v
    Model (use TracksMediaUsage)  ─►  MediaUsageService::syncForModel
                                             │
                                             ├─ extractPaths(model)  ── строки/JSON → пути по ext
                                             ├─ findOrCreateMediaFile(path)  ── регистрация в media_files
                                             └─ diff existing vs desired usages  ── добавить/удалить
```

`media:sync` = обход всего диска + чистка «сирот» + прогон всех моделей
с трейтом через `syncForModel`.

---

## Шаг 1. Миграция

`database/migrations/xxxx_xx_xx_xxxxxx_create_media_files_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id()->startingValue(1001);

            $table->string('path')->unique();
            $table->string('disk')->default('public');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type')->default('other');

            $table->string('title')->nullable();
            $table->string('alt')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('type');
            $table->index('mime_type');
        });

        Schema::create('media_file_usages', function (Blueprint $table) {
            $table->id()->startingValue(1001);

            $table->foreignId('media_file_id')
                ->constrained('media_files')
                ->cascadeOnDelete();

            $table->morphs('usable');
            $table->string('field');

            $table->timestamps();

            $table->unique(
                ['media_file_id', 'usable_type', 'usable_id', 'field'],
                'media_file_usages_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_file_usages');
        Schema::dropIfExists('media_files');
    }
};
```

---

## Шаг 2. Enum

`app/Enums/MediaFileType.php`:

```php
<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MediaFileType: string implements HasColor, HasLabel, HasIcon
{
    case Image = 'image';
    case Document = 'document';
    case Other = 'other';

    public function getColor(): ?string
    {
        return match ($this) {
            self::Image => 'success',
            self::Document => 'info',
            self::Other => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Image => __('panel.media_type_image'),
            self::Document => __('panel.media_type_document'),
            self::Other => __('panel.media_type_other'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Image => 'heroicon-o-photo',
            self::Document => 'heroicon-o-document',
            self::Other => 'heroicon-o-paper-clip',
        };
    }

    public static function fromMimeType(?string $mimeType): self
    {
        if (!$mimeType) {
            return self::Other;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return self::Image;
        }

        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ])) {
            return self::Document;
        }

        return self::Other;
    }
}
```

---

## Шаг 3. Модели

`app/Models/MediaFile.php`:

```php
<?php

namespace App\Models;

use App\Enums\MediaFileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    protected $fillable = [
        'path', 'disk', 'filename', 'mime_type', 'size',
        'type', 'title', 'alt', 'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'type' => MediaFileType::class,
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(MediaFileUsage::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        try {
            return Storage::disk($this->disk)->url($this->path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    protected static function booted(): void
    {
        // Убери, если в целевом проекте нет кеш-тэгов 'content'.
        static::deleted(fn () => cache()->tags(['content'])->flush());
    }
}
```

`app/Models/MediaFileUsage.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaFileUsage extends Model
{
    protected $fillable = ['media_file_id', 'usable_type', 'usable_id', 'field'];

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }
}
```

---

## Шаг 4. Трейт-маркер

`app/Models/Concerns/TracksMediaUsage.php`:

```php
<?php

namespace App\Models\Concerns;

use App\Models\MediaFileUsage;
use App\Services\MediaUsageService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait TracksMediaUsage
{
    public static function bootTracksMediaUsage(): void
    {
        static::saved(function ($model) {
            app(MediaUsageService::class)->syncForModel($model);
        });

        static::deleted(function ($model) {
            app(MediaUsageService::class)->removeAllForModel($model);
        });
    }

    public function mediaUsages(): MorphMany
    {
        return $this->morphMany(MediaFileUsage::class, 'usable');
    }
}
```

---

## Шаг 5. Сервис

`app/Services/MediaUsageService.php`:

```php
<?php

namespace App\Services;

use App\Enums\MediaFileType;
use App\Models\MediaFile;
use App\Models\MediaFileUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaUsageService
{
    private const FILE_EXTENSIONS = [
        'jpg','jpeg','png','gif','webp','svg','ico','bmp','avif',
        'pdf','doc','docx','xls','xlsx','txt','csv',
        'mp4','mp3','wav','ogg','webm',
        'zip','rar','7z',
    ];

    private const SKIP_ATTRIBUTES = [
        'id','created_at','updated_at','published_at','deleted_at',
        'slug','password','remember_token','email_verified_at',
    ];

    public function syncForModel(Model $model): void
    {
        $currentPaths = $this->extractPaths($model);

        $existingUsages = MediaFileUsage::query()
            ->where('usable_type', $model->getMorphClass())
            ->where('usable_id', $model->getKey())
            ->get();

        $existingByKey = $existingUsages->keyBy(
            fn (MediaFileUsage $u) => $u->media_file_id.':'.$u->field
        );

        $desiredUsages = [];

        foreach ($currentPaths as $field => $paths) {
            foreach ($paths as $path) {
                $mediaFile = $this->findOrCreateMediaFile($path);
                if (!$mediaFile) continue;

                $key = $mediaFile->id.':'.$field;
                $desiredUsages[$key] = [
                    'media_file_id' => $mediaFile->id,
                    'field' => $field,
                ];
            }
        }

        foreach ($existingByKey as $key => $usage) {
            if (!isset($desiredUsages[$key])) {
                $usage->delete();
            }
        }

        foreach ($desiredUsages as $key => $data) {
            if (!$existingByKey->has($key)) {
                MediaFileUsage::firstOrCreate([
                    'media_file_id' => $data['media_file_id'],
                    'usable_type' => $model->getMorphClass(),
                    'usable_id' => $model->getKey(),
                    'field' => $data['field'],
                ]);
            }
        }
    }

    public function removeAllForModel(Model $model): void
    {
        MediaFileUsage::query()
            ->where('usable_type', $model->getMorphClass())
            ->where('usable_id', $model->getKey())
            ->delete();
    }

    public function extractPaths(Model $model): array
    {
        $result = [];

        foreach ($model->getAttributes() as $key => $value) {
            if (in_array($key, self::SKIP_ATTRIBUTES, true)) continue;
            if ($value === null || is_bool($value) || is_numeric($value)) continue;
            if (!is_string($value)) continue;

            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                $paths = $this->extractFilePathsRecursive($decoded);
                if ($paths) $result[$key] = $paths;
            } elseif ($this->looksLikeFilePath($value)) {
                $result[$key] = [$value];
            }
        }

        return $result;
    }

    private function extractFilePathsRecursive(mixed $data): array
    {
        $paths = [];

        if (is_string($data)) {
            if ($this->looksLikeFilePath($data)) $paths[] = $data;
            return $paths;
        }

        if (is_array($data)) {
            foreach ($data as $value) {
                $paths = array_merge($paths, $this->extractFilePathsRecursive($value));
            }
        }

        return $paths;
    }

    private function looksLikeFilePath(string $value): bool
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) return false;

        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        if (!in_array($extension, self::FILE_EXTENSIONS, true)) return false;

        if (!str_contains($value, '/')) return false;

        return true;
    }

    private function findOrCreateMediaFile(string $path, string $disk = 'public'): ?MediaFile
    {
        $existing = MediaFile::where('path', $path)->where('disk', $disk)->first();
        if ($existing) return $existing;

        if (!Storage::disk($disk)->exists($path)) return null;

        $mimeType = null;
        $size = 0;

        try {
            $mimeType = Storage::disk($disk)->mimeType($path);
            $size = Storage::disk($disk)->size($path);
        } catch (\Throwable) {
        }

        return MediaFile::firstOrCreate(
            ['path' => $path, 'disk' => $disk],
            [
                'filename' => basename($path),
                'mime_type' => $mimeType,
                'size' => $size,
                'type' => MediaFileType::fromMimeType($mimeType)->value,
                'uploaded_by' => auth()->id(),
            ]
        );
    }

    public function registerFile(string $path, string $disk = 'public'): ?MediaFile
    {
        return $this->findOrCreateMediaFile($path, $disk);
    }
}
```

---

## Шаг 6. Артизан-команда

`app/Console/Commands/MediaSyncCommand.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Concerns\TracksMediaUsage;
use App\Models\MediaFile;
use App\Services\MediaUsageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MediaSyncCommand extends Command
{
    protected $signature = 'media:sync {--usages-only : Only rebuild usages, skip file scan}';

    protected $description = 'Scan storage and index files into media library, then rebuild usages';

    public function handle(MediaUsageService $service): int
    {
        if (!$this->option('usages-only')) {
            $this->scanFiles();
            $this->cleanOrphans();
        }

        $this->rebuildUsages($service);

        $this->info('Done.');
        return self::SUCCESS;
    }

    private function scanFiles(): void
    {
        $this->info('Scanning storage/app/public...');

        $disk = Storage::disk('public');
        $files = $disk->allFiles();
        $bar = $this->output->createProgressBar(count($files));
        $created = 0;
        $service = app(MediaUsageService::class);

        foreach ($files as $path) {
            if (str_starts_with($path, 'livewire-tmp/') || str_starts_with(basename($path), '.')) {
                $bar->advance();
                continue;
            }

            $existing = MediaFile::where('path', $path)->where('disk', 'public')->first();

            if (!$existing && $service->registerFile($path)) {
                $created++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Indexed {$created} new files.");
    }

    private function cleanOrphans(): void
    {
        $this->info('Cleaning orphaned records...');

        $removed = 0;
        MediaFile::where('disk', 'public')->chunk(200, function ($files) use (&$removed) {
            foreach ($files as $file) {
                if (!$file->existsOnDisk()) {
                    $file->usages()->delete();
                    $file->delete();
                    $removed++;
                }
            }
        });

        $this->info("Removed {$removed} orphaned records.");
    }

    private function rebuildUsages(MediaUsageService $service): void
    {
        $this->info('Rebuilding usages...');

        foreach ($this->getTrackedModelClasses() as $class) {
            $this->info("  Processing {$class}...");

            $class::query()->chunk(100, function ($models) use ($service) {
                foreach ($models as $model) {
                    $service->syncForModel($model);
                }
            });
        }
    }

    private function getTrackedModelClasses(): array
    {
        $models = [];
        $files = File::allFiles(app_path('Models'));

        foreach ($files as $file) {
            if ($file->getRelativePath() === 'Concerns') continue;

            $class = 'App\\Models\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            if (!class_exists($class)) continue;

            if (in_array(TracksMediaUsage::class, class_uses_recursive($class), true)) {
                $models[] = $class;
            }
        }

        return $models;
    }
}
```

---

## Шаг 7. Helper для имён загружаемых файлов

Дописать в `app/helpers.php` (или создать, подключить через
`composer.json → autoload.files`):

```php
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

if (! function_exists('generate_uploaded_file_name')) {
    function generate_uploaded_file_name(TemporaryUploadedFile $file, int $limit = 20): string
    {
        return str(
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
        )
            ->slug()
            ->limit($limit)
            ->append('-'.time().'.'.$file->getClientOriginalExtension())
            ->toString();
    }
}
```

---

## Шаг 8. Filament-ресурс

`app/Filament/Resources/MediaFiles/MediaFileResource.php`:

```php
<?php

namespace App\Filament\Resources\MediaFiles;

use App\Filament\Resources\MediaFiles\Pages\CreateMediaFile;
use App\Filament\Resources\MediaFiles\Pages\EditMediaFile;
use App\Filament\Resources\MediaFiles\Pages\ListMediaFiles;
use App\Filament\Resources\MediaFiles\Schemas\MediaFileForm;
use App\Filament\Resources\MediaFiles\Tables\MediaFilesTable;
use App\Models\MediaFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaFileResource extends Resource
{
    protected static ?string $model = MediaFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::FolderOpen;

    protected static ?int $navigationSort = 90;

    public static function getModelLabel(): string       { return __('panel.media_file'); }
    public static function getPluralModelLabel(): string { return __('panel.media_files'); }
    public static function getNavigationLabel(): string  { return __('panel.media_files_list'); }

    public static function form(Schema $schema): Schema
    {
        return MediaFileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaFilesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaFiles::route('/'),
            'create' => CreateMediaFile::route('/create'),
            'edit' => EditMediaFile::route('/{record}/edit'),
        ];
    }
}
```

### Schemas/MediaFileForm.php

```php
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
                        ->maxSize(10240) // 10MB
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
                            if ($usages->isEmpty()) return __('panel.media_not_used');

                            return $usages->map(function ($usage) {
                                $model = $usage->usable;
                                if (!$model) return null;
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
```

### Tables/MediaFilesTable.php

```php
<?php

namespace App\Filament\Resources\MediaFiles\Tables;

use App\Enums\MediaFileType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class MediaFilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('path')->label(__('panel.thumbnail'))
                    ->disk('public')->width(60)->imageHeight(60)->square()
                    ->getStateUsing(fn ($record) => $record->type === MediaFileType::Image ? $record->path : null),

                TextColumn::make('filename')->label(__('panel.file_name'))
                    ->searchable()->sortable()->limit(40)
                    ->tooltip(fn ($record) => $record->path),

                TextColumn::make('type')->label(__('panel.type'))->badge()->sortable(),

                TextColumn::make('human_size')->label(__('panel.size'))
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('size', $direction)),

                TextColumn::make('usages_count')->label(__('panel.media_usages_count'))
                    ->counts('usages')->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('title')->label(__('panel.title'))
                    ->searchable()->limit(30)->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mime_type')->label(__('panel.mime_type'))
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->label(__('panel.type'))
                    ->options(MediaFileType::class)->multiple(),

                TernaryFilter::make('has_usages')->label(__('panel.media_used'))
                    ->queries(
                        true: fn ($query) => $query->has('usages'),
                        false: fn ($query) => $query->doesntHave('usages'),
                    ),
            ], layout: FiltersLayout::AboveContent)->deferFilters(false)
            ->recordActions([
                EditAction::make()->label('')->iconSize('md')->tooltip(__('panel.edit')),

                Action::make('copy_url')->label('')
                    ->icon('heroicon-o-clipboard')->iconSize('md')
                    ->tooltip(__('panel.media_copy_url'))->color('info')
                    ->url(fn ($record) => $record->url)
                    ->extraAttributes(fn ($record) => [
                        'x-on:click.prevent' => "window.navigator.clipboard.writeText('".addslashes($record->url)."'); \$tooltip('".addslashes(__('panel.media_url_copied'))."')",
                    ]),

                DeleteAction::make()->label('')->iconSize('md')->tooltip(__('panel.delete'))
                    ->modalDescription(function ($record) {
                        $usages = $record->usages()->with('usable')->get();
                        if ($usages->isEmpty()) return null;

                        $list = $usages->map(function ($usage) {
                            $model = $usage->usable;
                            if (!$model) return null;
                            $type = class_basename($model);
                            $name = $model->title ?? $model->name ?? $model->full_name ?? $model->author ?? "#{$model->getKey()}";
                            return "- {$type}: {$name} ({$usage->field})";
                        })->filter()->join("\n");

                        return __('panel.media_confirm_delete_used')."\n\n".$list;
                    })
                    ->before(function ($record) {
                        if ($record->existsOnDisk()) {
                            Storage::disk($record->disk)->delete($record->path);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->existsOnDisk()) {
                                    Storage::disk($record->disk)->delete($record->path);
                                }
                            }
                        }),
                ]),
            ]);
    }
}
```

### Pages/ListMediaFiles.php

```php
<?php

namespace App\Filament\Resources\MediaFiles\Pages;

use App\Filament\Resources\MediaFiles\MediaFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMediaFiles extends ListRecords
{
    protected static string $resource = MediaFileResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
```

### Pages/CreateMediaFile.php

```php
<?php

namespace App\Filament\Resources\MediaFiles\Pages;

use App\Enums\MediaFileType;
use App\Filament\Resources\MediaFiles\MediaFileResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateMediaFile extends CreateRecord
{
    protected static string $resource = MediaFileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $path = $data['path'] ?? null;

        if ($path && Storage::disk('public')->exists($path)) {
            $data['filename'] = basename($path);
            $data['mime_type'] = Storage::disk('public')->mimeType($path);
            $data['size'] = Storage::disk('public')->size($path);
            $data['type'] = MediaFileType::fromMimeType($data['mime_type'])->value;
        }

        $data['disk'] = 'public';
        $data['uploaded_by'] = auth()->id();

        return $data;
    }
}
```

### Pages/EditMediaFile.php

```php
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
                    if ($usages->isEmpty()) return null;

                    $list = $usages->map(function ($usage) {
                        $model = $usage->usable;
                        if (!$model) return null;
                        $type = class_basename($model);
                        $name = $model->title ?? $model->name ?? $model->full_name ?? $model->author ?? "#{$model->getKey()}";
                        return "- {$type}: {$name} ({$usage->field})";
                    })->filter()->join("\n");

                    return __('panel.media_confirm_delete_used')."\n\n".$list;
                })
                ->before(function () {
                    $record = $this->getRecord();
                    if ($record->existsOnDisk()) {
                        Storage::disk($record->disk)->delete($record->path);
                    }
                }),
        ];
    }
}
```

---

## Шаг 9. Медиа-пикер (modal + grid)

`app/Filament/Forms/Components/MediaPickerAction.php`:

```php
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
    /**
     * Возвращает Closure — каждый клон компонента (например внутри Repeater)
     * получает свою Action-инстанцию с правильным контекстом схемы.
     */
    public static function make(
        string $fieldName,
        bool $imagesOnly = false,
        bool $multiple = false,
        ?array $acceptedMimeTypes = null,
        $maxSize = null,
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
            ->action(function (array $data, Get $get, Set $set) use ($fieldName, $multiple) {
                $selected = $data['media_file_ids'] ?? null;

                $ids = $multiple
                    ? (is_array($selected) ? $selected : [$selected])
                    : ($selected ? [$selected] : []);

                $ids = array_filter($ids);
                if (empty($ids)) return;

                $paths = MediaFile::whereIn('id', $ids)->pluck('path')->toArray();
                if (empty($paths)) return;

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
```

`app/Filament/Forms/Components/MediaGrid.php`:

```php
<?php

namespace App\Filament\Forms\Components;

use App\Enums\MediaFileType;
use App\Models\MediaFile;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MediaGrid extends Field
{
    protected string $view = 'forms.components.media-grid';

    protected bool $imagesOnly = false;
    protected bool $multiple = false;
    protected ?array $acceptedMimeTypes = null;
    protected ?int $maxSize = null;
    protected int $perPage = 36;

    public function imagesOnly(bool $v = true): static      { $this->imagesOnly = $v; return $this; }
    public function multiple(bool $v = true): static        { $this->multiple = $v; return $this; }
    public function acceptedMimeTypes(?array $v): static    { $this->acceptedMimeTypes = $v; return $this; }
    public function maxSize(?int $v): static                { $this->maxSize = $v; return $this; }
    public function perPage(int $v): static                 { $this->perPage = $v; return $this; }

    public function getImagesOnly(): bool           { return $this->imagesOnly; }
    public function getMultiple(): bool             { return $this->multiple; }
    public function getAcceptedMimeTypes(): ?array  { return $this->acceptedMimeTypes; }
    public function getPerPage(): int               { return $this->perPage; }

    public function getMediaFiles(Get $get): LengthAwarePaginator
    {
        $search = trim((string) ($get('media_search') ?? ''));
        $page = max(1, (int) ($get('media_page') ?? 1));

        $query = MediaFile::query()
            ->where('disk', 'public')
            ->orderByDesc('created_at');

        if ($this->acceptedMimeTypes) {
            $query->whereIn('mime_type', $this->acceptedMimeTypes);
        } elseif ($this->imagesOnly) {
            $query->where('type', MediaFileType::Image->value);
        }

        if ($this->maxSize) {
            $query->where('size', '<=', $this->maxSize * 1024);
        }

        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->where('filename', 'like', $like)
                    ->orWhere('title', 'like', $like)
                    ->orWhere('path', 'like', $like);
            });
        }

        return $query->paginate(perPage: $this->perPage, page: $page);
    }
}
```

`resources/views/forms/components/media-grid.blade.php`:

```blade
@php
    $paginator = $getMediaFiles($makeGetUtility());
    $multiple = $getMultiple();
    $statePath = $getStatePath();

    $files = $paginator->getCollection()->map(fn ($f) => [
        'id' => $f->id,
        'path' => $f->path,
        'filename' => $f->filename,
        'title' => $f->title,
        'url' => $f->url,
        'type' => $f->type->value,
        'human_size' => $f->human_size,
    ])->values()->all();

    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $total = $paginator->total();
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();

    $pageStatePath = preg_replace('/[^.]+$/', 'media_page', $statePath);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            selected: @entangle($statePath),
            page: @entangle($pageStatePath).live,
            multiple: @js($multiple),

            isSelected(id) {
                if (this.multiple) {
                    return Array.isArray(this.selected) && this.selected.includes(id)
                }
                return this.selected === id
            },

            toggle(id) {
                if (this.multiple) {
                    if (! Array.isArray(this.selected)) this.selected = []
                    const idx = this.selected.indexOf(id)
                    if (idx === -1) {
                        this.selected = [...this.selected, id]
                    } else {
                        this.selected = this.selected.filter((i) => i !== id)
                    }
                } else {
                    this.selected = this.selected === id ? null : id
                }
            },
        }"
        class="space-y-3"
    >
        <div class="grid max-h-[60vh] grid-cols-4 gap-2 overflow-y-auto p-1 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10">
            @forelse ($files as $file)
                <div
                    @click="toggle({{ $file['id'] }})"
                    class="relative cursor-pointer rounded-lg border-2 p-1 transition-all duration-150 hover:shadow-md"
                    :class="isSelected({{ $file['id'] }})
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10 ring-2 ring-primary-500/50'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                >
                    <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded bg-gray-100 dark:bg-gray-800">
                        @if ($file['type'] === 'image')
                            <img src="{{ $file['url'] }}" alt="{{ $file['filename'] }}" class="h-full w-full object-cover" loading="lazy" />
                        @else
                            <span class="text-3xl">{{ $file['type'] === 'document' ? '📄' : '📎' }}</span>
                        @endif
                    </div>

                    <p class="mt-1 truncate text-center text-xs text-gray-600 dark:text-gray-400"
                       title="{{ $file['filename'] }} ({{ $file['human_size'] }})">
                        {{ $file['title'] ?: $file['filename'] }}
                    </p>

                    <div
                        x-show="isSelected({{ $file['id'] }})"
                        x-transition
                        class="bg-primary-500 absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full text-white shadow"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('panel.media_no_files_found') }}
                </div>
            @endforelse
        </div>

        @if ($total > 0)
            <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $from }}–{{ $to }} / {{ $total }}
                </div>

                @if ($lastPage > 1)
                    <div class="flex items-center gap-2">
                        <button type="button"
                            @click="if (page > 1) page = page - 1"
                            :disabled="page <= 1"
                            class="rounded-md bg-white px-3 py-1.5 text-sm text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/20 dark:hover:bg-white/10">
                            ←
                        </button>
                        <span class="text-sm text-gray-700 dark:text-gray-200">
                            <span x-text="page"></span> / {{ $lastPage }}
                        </span>
                        <button type="button"
                            @click="if (page < {{ $lastPage }}) page = page + 1"
                            :disabled="page >= {{ $lastPage }}"
                            class="rounded-md bg-white px-3 py-1.5 text-sm text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/20 dark:hover:bg-white/10">
                            →
                        </button>
                    </div>
                @endif

                @if ($multiple)
                    <div x-show="Array.isArray(selected) && selected.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('panel.media_selected') }}: <span x-text="selected.length"></span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-dynamic-component>
```

---

## Шаг 10. Ключи локализации

Добавить в `lang/ru/panel.php` (и `en/panel.php` при необходимости):

```php
'media_file' => 'Медиа-файл',
'media_files' => 'Медиа-файлы',
'media_files_list' => 'Медиа-файлы',
'media_upload' => 'Загрузка',
'media_file_info' => 'Информация о файле',
'media_type_image' => 'Изображение',
'media_type_document' => 'Документ',
'media_type_other' => 'Другое',
'media_used' => 'Используется',
'media_used_in' => 'Используется в',
'media_not_used' => 'Не используется',
'media_usages_count' => 'Использований',
'media_copy_url' => 'Скопировать URL',
'media_url_copied' => 'URL скопирован',
'media_confirm_delete_used' => 'Файл используется в:',
'media_choose_from_library' => 'Выбрать из библиотеки',
'media_picker_title' => 'Библиотека медиа',
'media_no_files_found' => 'Файлы не найдены',
'media_selected' => 'Выбрано',
'search' => 'Поиск',
'thumbnail' => 'Превью',
'file_name' => 'Имя файла',
'mime_type' => 'MIME-тип',
'size' => 'Размер',
'title' => 'Заголовок',
'alt' => 'Alt',
'file' => 'Файл',
'settings' => 'Настройки',
'url' => 'URL',
'type' => 'Тип',
'edit' => 'Редактировать',
'delete' => 'Удалить',
'created_at' => 'Создано',
```

---

## Шаг 11. Подключение к моделям

К каждой модели, у которой есть поля с файлами (или JSON-контент с
файлами), добавляем один трейт:

```php
use App\Models\Concerns\TracksMediaUsage;

class Trip extends Model
{
    use TracksMediaUsage;
    // ...
}
```

Всё. Никаких списков полей — сервис сам сканирует все атрибуты.

Опционально: в местах, где есть `FileUpload`, добавить hint-action:

```php
FileUpload::make('thumbnail')
    ->image()
    ->disk('public')->directory('media')
    ->getUploadedFileNameForStorageUsing(
        fn (TemporaryUploadedFile $file): string => generate_uploaded_file_name($file)
    )
    ->hintAction(MediaPickerAction::make('thumbnail', imagesOnly: true)),

// multiple
FileUpload::make('images')
    ->multiple()
    ->disk('public')->directory('media')
    ->hintAction(MediaPickerAction::make('images', imagesOnly: true, multiple: true)),

// только SVG
FileUpload::make('icon')
    ->disk('public')->directory('media')
    ->hintAction(MediaPickerAction::make('icon', acceptedMimeTypes: ['image/svg+xml'])),
```

---

## Шаг 12. Первичная индексация

Один раз на существующем проекте:

```bash
php artisan migrate
php artisan storage:link
php artisan media:sync            # проиндексирует диск + пересоберёт usages
# позже, для пересборки только usages:
php artisan media:sync --usages-only
```

---

## Как это работает — ключевые инварианты

1. **Трейт-маркер, не конфигурация.** Единственное требование к модели —
   `use TracksMediaUsage`. Никаких списков полей в реестре: сервис
   сканирует все атрибуты автоматически.
2. **`extractPaths` смотрит на атрибуты, не на приведённые значения.**
   Работает и со строкой-путём, и с JSON-полями (`getAttributes()` даёт
   сырое значение, потом `json_decode` при необходимости).
3. **Идентификация пути:** относительный (без `http://`), содержит `/`,
   имеет расширение из allowlist. Внешние URL и мусор отсекаются.
4. **Синк = diff, не rebuild.** Существующие usages сравниваются с желаемыми
   по ключу `media_file_id:field` — лишние удаляются, недостающие
   создаются. Без каскадного пересоздания.
5. **`findOrCreateMediaFile` создаёт запись только если файл реально
   лежит на диске** — так синк не наплодит фантомных `media_files` для
   строк, случайно похожих на путь.
6. **Удаление медиа-записи через админку** дополнительно физически
   удаляет файл с диска (`before()` в `DeleteAction`).
7. **`media:sync` двухфазная:** сначала диск→БД + чистка сирот, потом
   пересборка usages для всех трекаемых моделей. `--usages-only`
   пропускает первую фазу.
8. **Пикер отдаёт path**, не id. `FileUpload` хранит path — единая
   ниточка через всю систему. Никаких id-в-контенте.
