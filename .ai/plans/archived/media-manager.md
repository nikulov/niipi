# Медиа-менеджер (archived)

Реализовано и смерджено в `staging` 2026-07-29.

## Цель

Единый медиа-менеджер: каталог загруженных файлов диска `public`, автоматический
трекинг «где какой файл используется» (модель + поле), Filament-ресурс + модальный
пикер как `hintAction` для `FileUpload`.

## Что сделано

**Инфраструктура:**
- Миграция `media_files` + `media_file_usages` (морф-пивот).
- Enum `MediaFileType` (image/document/other) + `fromMimeType()`.
- Модели `MediaFile`, `MediaFileUsage`.
- Трейт-маркер `TracksMediaUsage` (bootTraits: `saved` → sync, `deleted` → cleanup).
- `MediaUsageService` — extract paths из атрибутов+JSON, diff-sync usages, findOrCreate.
- Артизан `media:sync` — двухфазная (скан диска → БД, пересборка usages). Skip:
  `livewire-tmp/`, **`forms/`** (защита `FormSubmissionFile`).

**Filament:**
- Ресурс `MediaFileResource` под группой навигации «Медиа» (Admin+Editor+Viewer).
- `MediaFilePolicy` по конвенции проекта: Admin=all, Editor=CRU, Viewer=R.
- Табличная страница с ImageColumn превью, фильтрами по типу и used/unused, badge
  usages_count, копированием URL.
- Форма с fieldset upload/meta + infolist «где используется».
- Модальный пикер `MediaPickerAction` + кастомный `MediaGrid` field + Blade с Alpine
  (grid, поиск с debounce 500ms, пагинация через entangle).

**Интеграция:**
- Трейт подключён к: `Page`, `Post`, `Project`, `Footer`, `GlobalSetting`.
- `MediaFile::deleted` → `Cache::forget('footer.data')` + `Cache::forget('global_settings')`
  (проектные кэши, где могут осесть пути).
- Хелпер `generate_uploaded_file_name()` в `app/helpers.php`.
- **Все 16 `FileUpload::make(...)`** в проекте получили `->hintAction(MediaPickerAction::make(...))`
  с фильтрами, точно соответствующими `acceptedFileTypes` / `->image()` / `multiple`
  каждого поля. Ключевые: SVG-only для footer/иконок, mixed types для Cards и
  Form.user_mail_attachments, imagesOnly для остальных.
- `theme.css` — добавлен `@source '../../../../resources/views/forms/**/*'` для
  Tailwind (иначе утилиты `media-grid.blade.php` не попадают в бандл).

**Тесты:** 22 новых (9 unit сервиса + 6 feature команды + 7 feature ресурса и политики).
Полный suite 244 passed, 749 assertions, регрессий нет.

**Данные на момент архива:** 84 индексированных `MediaFile`, 85 `usages`; папка `forms/`
корректно игнорируется.

## Что НЕ вошло (осознанно)

- **Автоочистка ссылок при удалении медиа-файла.** Сейчас удаление MediaFile
  физически стирает файл + запись + FK cascade `usages`, но пути в JSON блоков /
  скалярных полях остаются — публика показывает битую картинку до ручной правки.
  Разобрано в чате 2026-07-29, решено оставить как есть; вариант A (`MediaFile::deleting`
  → `detachFromUsables`) с list-vs-assoc нормализацией — кандидат для отдельной
  задачи, если UX начнёт мешать.
- Bulk-миграция для протухших `images//foo.jpg` путей в Page #1000/1001/1002 +
  Post #1024 (легаси от коммита `5b01f5d` до его же `refactoring`-фикса, декабрь 2025).
  Мой `media:sync` их отразил как 4 «дубля» с `//` — рядом с чистыми.
  Публика работает (браузер схлопывает), можно чистить вручную через UI позже.
- Замена existing FileUpload closure'ов (`getUploadedFileNameForStorageUsing`) на
  новый хелпер — отдельный cleanup.
- Дедупликация по хешу.

## Коммиты

| Дата | SHA | Описание |
|---|---|---|
| 2026-07-28 | `43fe3fc` | план `.ai/plans/media-manager/` |
| 2026-07-28 | `7c4fd8c` | инфра медиа + ресурс + пикер + политика + тесты |
| 2026-07-29 | `cb7ae5c` | пикер в 16 сайтов + чистка GlobalSettingForm + theme.css @source |

## Ключевые файлы (для контекста будущих сессий)

- `app/Services/MediaUsageService.php` — сердце трекинга (extract + diff-sync).
- `app/Models/Concerns/TracksMediaUsage.php` — маркер для моделей.
- `app/Console/Commands/MediaSyncCommand.php` — reindex, ВНИМАНИЕ на skip-prefixes.
- `app/Filament/Forms/Components/MediaPickerAction.php` — фабрика Closure, важно для
  Repeater-контекста.
- `app/Filament/Forms/Components/MediaGrid.php` + `resources/views/forms/components/media-grid.blade.php`
  — Field + Blade с Alpine.
- `app/Filament/Resources/MediaFiles/` — ресурс со всеми страницами/схемой/таблицей.
- `app/Policies/MediaFilePolicy.php` — матрица прав по проектной конвенции.
