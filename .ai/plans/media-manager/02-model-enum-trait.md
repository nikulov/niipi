# Шаг 02. Enum, модели, трейт

## Концепт

Инфраструктурные классы: тип файла (enum), реестр (MediaFile), пивот
(MediaFileUsage), маркер-трейт для трекаемых моделей.

## Что делаем

1. Enum `App\Enums\MediaFileType` — из промта. Реализует
   `HasColor + HasLabel + HasIcon`, статический метод `fromMimeType`.
2. Модель `App\Models\MediaFile` — из промта, **с проектной правкой
   `booted()`.** Исходную строку `cache()->tags(['content'])->flush()`
   удалить (тега нет). Вместо неё — сброс real-каналов кэша, где может
   осесть путь к удалённому файлу:
   ```php
   use Illuminate\Support\Facades\Cache;

   protected static function booted(): void
   {
       static::deleted(function () {
           Cache::forget(Footer::cacheKey());   // footer.data
           Cache::forget('global_settings');    // GlobalSetting::getSetting()
       });
   }
   ```
   `use App\Models\Footer;` не нужен — `MediaFile` и `Footer` живут в
   одном namespace `App\Models`. Ссылка `Footer::cacheKey()` резолвится
   напрямую. Почему именно эти два ключа — см. «Ключевые решения и
   контекст» в [README.md](README.md).
   Тэги `news`/`projects`/`categories` не флашим — их сбрасывают сами
   Post/Project/Category на save/delete.
3. Модель `App\Models\MediaFileUsage` — из промта.
4. Трейт `App\Models\Concerns\TracksMediaUsage` — из промта. Ничего
   специфичного для проекта менять не надо.

## Файлы

- **NEW** `app/Enums/MediaFileType.php`
- **NEW** `app/Models/MediaFile.php`
- **NEW** `app/Models/MediaFileUsage.php`
- **NEW** `app/Models/Concerns/TracksMediaUsage.php`

## References

- Паттерн enum со статусом — [patterns/enum-with-color-label.md](../../patterns/enum-with-color-label.md).
- Ключи локализации `panel.media_type_*` добавляются в
  [08-localization.md](08-localization.md) — при работе с enum они уже
  предполагаются.
- Пример модели с `HasMany` — `app/Models/Post.php`.
- `Models/Concerns/` уже используется (пример:
  `HasSectionOptions.php`).

## Gotchas

- MediaFile НЕ использует `TracksMediaUsage` — иначе рекурсия. Не
  добавляй.
- `getUrlAttribute()` возвращает `?string` через `Storage::disk()->url()`
  — в try/catch, потому что для некоторых дисков `url()` может кинуть
  `RuntimeException` (например, если у диска нет `url` в конфиге).
- Cast `'type' => MediaFileType::class` требует, чтобы `type` в БД был
  строкой (`string default 'other'`) — это из миграции уже.

## Checklist

- [ ] Enum создан, `fromMimeType` возвращает `Image` для `image/*`,
      `Document` для перечисленных офисных mime, иначе `Other`.
- [ ] `MediaFile` содержит relations `usages()`, `uploadedBy()`,
      аксессоры `url`, `human_size`, метод `existsOnDisk()`.
- [ ] `MediaFile::booted()` НЕ содержит `cache()->tags(['content'])`;
      вместо него — `Cache::forget(Footer::cacheKey())` +
      `Cache::forget('global_settings')`.
- [ ] `MediaFileUsage` содержит relation `mediaFile()`, `usable()`.
- [ ] Трейт `TracksMediaUsage` регистрирует хуки `saved` и `deleted`.
- [ ] `sail bin pint --dirty` без замечаний.
