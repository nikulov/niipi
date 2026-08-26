# Копирование сущности — архив

## Цель

Универсальное row-действие «Копировать» в Filament-таблицах Post, Project,
Page, Form. Полная копия записи в транзакции, суффиксы «(копия N)» /
`-copy-N`, сброс статуса/активности, клон пивотов и HasMany.

## Что сделано

- **Сервис `app/Services/ModelDuplicator.php`** — `duplicate(Model)` в
  `DB::transaction`. Константы `TITLE_WORD='копия'`, `SLUG_WORD='copy'`.
  Парсит существующие суффиксы, считает `nextCopyNumber` по обеим
  колонкам (title + slug), `replicate()` с фильтрацией через реальный
  `getColumnListing($table)` (защита от `withCount`-виртуальных колонок),
  вызывает `prepareDuplicate()`, `save()`, `copyRelationsTo()`.
- **Трейт `app/Models/Concerns/Duplicatable.php`** — `duplicate()`,
  `duplicateTitleColumn(): 'title'`, `duplicateSlugColumn(): ?string
  = 'slug'`, абстрактный `prepareDuplicate()`, no-op `copyRelationsTo()`.
- **`app/Filament/Actions/CopyAction.php`** — имя `copy` (не `replicate`,
  чтобы не конфликтовать со встроенным `ReplicateAction` v4), иконка
  `Heroicon::DocumentDuplicate`, `requiresConfirmation()`. Явная авторизация
  через `Gate::allows('create', $record::class)` — маппинг «копирование ≈
  создание», т.к. Filament v4 автоавторизует только built-in actions.
- **Модели:**
  - `Post`, `Project`: `use Duplicatable`, Draft + `published_at=null`,
    `copyRelationsTo` — клон `categories` pivot.
  - `Page`: `use Duplicatable`, Draft + `published_at=null`, без пивотов.
  - `Form`: `use Duplicatable`, `duplicateTitleColumn='name'`,
    `duplicateSlugColumn=null`, `is_active=false`, `copyRelationsTo` —
    клон `fields` HasMany (без `submissions`).
- **Таблицы:** `CopyAction` вставлен Edit → Copy → Delete в Posts,
  Projects, Pages; Edit → Copy в Forms (Delete там нет).
- **Переводы** `lang/{ru,en}/panel.php`: `copy`, `copy_{post,project,page,form}`,
  `copy_*_confirm`, `*_copied`.
- **Тесты** `tests/Unit/Models/Concerns/Duplicatable{Post,Project,Page,Form}Test.php` —
  суффиксы «(копия)» / «(копия 2)» / «(копия 3)», сброс статуса,
  `published_at=null`, клон пивотов/HasMany, `submissions` не клонируются.
  `vendor/bin/sail artisan test --compact` — регрессий нет.
- **`.ai/`:** `file-map.md` (сервис, трейт, action), `conventions.md`
  (модели с копированием — трейт `Duplicatable`).

## Коммит

- `baa1e52` add copy row action for post/project/page/form

## Даты

- Начало: 2026-07-29
- Закрытие: 2026-07-30

## Boundaries (не входило)

- Копирование Category / User / Menu / Footer / GlobalSetting.
- Bulk-действие «скопировать выделенные».
- Копирование `FormSubmission`, `FormSubmissionFile`.
- Физический клон медиа-файлов (пути общие, `TracksMediaUsage` пересчитает).
- JSON-патч self-id (`patchDuplicatedContent`).
- Soft-delete-aware копирование.
