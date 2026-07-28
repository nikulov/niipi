# Медиа-менеджер

## Задача

Встроить единый медиа-менеджер: каталог загруженных файлов диска `public`
с автоматическим отслеживанием, где каждый файл используется (модель +
поле), с админ-CRUD на Filament и модальным пикером для `FileUpload`.

Ключевые свойства:

1. Регистр всех файлов в таблице `media_files` (path, mime, size, type,
   title, alt, uploaded_by).
2. Автотрекинг использований: пивот `media_file_usages` с
   `morphs('usable')` + `field`. Синк на `saved`/`deleted` модели.
3. **Без списков полей.** Сервис сам сканирует все атрибуты модели
   (строки + JSON) и находит пути к файлам по расширению + существованию
   на диске. Единственное условие — на модели трейт-маркер
   `TracksMediaUsage`.
4. Filament-ресурс со списком, фильтрами (тип / used–unused), превью,
   копированием URL, показом «где используется», конфирмом удаления.
5. Модальный медиа-пикер `MediaPickerAction::make('fieldName', ...)` как
   `hintAction` на `FileUpload`. Поиск + пагинация + грид на Alpine.
6. Артизан-команда `media:sync` для первичной индексации существующего
   диска и пересборки usages.

## Ключевые решения и контекст

- **Стек:** Laravel 12, Filament 4, Livewire 3.7, Alpine 3, Tailwind 4.
  Исходный промт заявлен под Filament 5 / Livewire 4, но фактические API
  в нём — Filament 4 / Livewire 3, что совпадает с проектом.
- **Навигация:** новая группа `Медиа` в
  `AdminPanelProvider::navigationGroups()`, перед `Настройки`.
- **Роли:** `RoleAccessResource` с `[Admin, Editor, Viewer]`. Ресурс
  использует существующий трейт из `app/Filament/Support/`.
- **Трейт `TracksMediaUsage` прикрепляем сразу к:** `Page`, `Post`,
  `Project`, `Footer`, `GlobalSetting`. `media:sync` после миграции
  соберёт все существующие usages. Модели, к которым трейт **не
  прикрепляем и почему:**
  - `Menu.top_items` / `footer_items` — JSON с url/label/page_slug, без
    путей к файлам (проверено).
  - `User` — нет файловых полей.
  - `FormSubmission`, `FormSubmissionFile` — управляют своими файлами
    через отдельную инфраструктуру (`SubmissionFilesStorer`); эти файлы
    в медиатеку **не должны попадать**, см. следующий пункт.
  - `Form.user_mail_attachments` — если хочется, можно добавить
    отдельно; сейчас пропускается вместе с папкой `forms/` (см. ниже).
- **`storage/app/public/forms/` — критично исключить** из скана
  `MediaSyncCommand`. Там лежат:
  - `forms/{form_id}/{submission_id}/*` — вложения к заявкам
    (модель `FormSubmissionFile`).
  - `forms/user-mail-attachments/*` — вложения к письмам форм
    (`Form.user_mail_attachments`).
  Индексация этих файлов даст admin'у возможность удалить их из
  медиа-библиотеки → потеря вложений заявок / писем. См.
  [04-artisan-command.md](04-artisan-command.md).
- **Кэш при удалении медиа-файла.** В исходном промте `MediaFile::booted()`
  флашит `cache()->tags(['content'])` — такого тега в проекте нет.
  Реальные кэши в проекте:
  - Тэги `news`, `projects`, `categories` — уже флашатся из самих моделей
    Post/Project/Category, от MediaFile флашить не надо.
  - Single-key `footer.data` (`Footer::cachedData()`) содержит
    `socialData` → пути к SVG-иконкам футера. При удалении файла из
    медиа-библиотеки кэш стухнет → битые иконки.
  - Single-key `global_settings` (`GlobalSetting::getSetting()`)
    содержит `favicon` → тот же риск.
  - Content HTML не кэшируется (см. `ContentRenderer.php:12` — «пока
    без кеша»), для Page/Post/Project флаш не нужен.
  Поэтому `MediaFile::booted()` **оставляем**, но с проектным
  содержимым:
  ```php
  protected static function booted(): void
  {
      static::deleted(function () {
          \Illuminate\Support\Facades\Cache::forget(\App\Models\Footer::cacheKey());
          \Illuminate\Support\Facades\Cache::forget('global_settings');
      });
  }
  ```
  См. [02-model-enum-trait.md](02-model-enum-trait.md).
- **`app/helpers.php`** уже подключен в `bootstrap/app.php:7` —
  дописываем `generate_uploaded_file_name()` без правки `composer.json`.
- **`BaseListRecords` не используем** — только стандартный
  `Filament\Resources\Pages\ListRecords`.

## Границы

Не входит:
- Замена всех существующих `FileUpload` closure на `generate_uploaded_file_name` в
  Post/Project/Page/Footer/Components/* (можно сделать отдельным PR-cleanup).
- Deduplication файлов по хешу содержимого — вне рамок; уникальность
  только по `path`.

## Роли и авторизация

В проекте строгая конвенция: у каждой модели своя `App\Policies\{Model}Policy`
(наследуется от `BasePolicy`), Filament автоподхватывает по имени.
`BasePolicy::before()` даёт Admin bypass. Ограничения Editor/Viewer
задаются в конкретной политике.

**Без `MediaFilePolicy` Filament разрешает всё** аутентифицированным
пользователям, прошедшим `canViewAny()` — Viewer сможет удалять файлы.
Это нарушает паттерн (в `PostPolicy` `delete` возвращает false даже
для Editor). Поэтому в план включена своя политика:

- **Admin** — всё (bypass).
- **Editor** — `viewAny`, `view`, `create`, `update`; `delete/deleteAny`
  = **false** (удалением файлов из библиотеки занимается только Admin —
  это опасная операция, физически удаляет файл с диска).
- **Viewer** — только `viewAny`, `view`; ни `create`, ни `update`,
  ни `delete`.

См. [06b-policy.md](06b-policy.md).

## Порядок работы

Каждый шаг — отдельный файл. Читать по одному в момент реализации.

1. [01-migration.md](01-migration.md) — таблицы `media_files` и
   `media_file_usages`.
2. [02-model-enum-trait.md](02-model-enum-trait.md) — enum
   `MediaFileType`, модели `MediaFile`, `MediaFileUsage`, трейт
   `TracksMediaUsage`.
3. [03-service.md](03-service.md) — `MediaUsageService`.
4. [04-artisan-command.md](04-artisan-command.md) — `media:sync`.
5. [05-helper.md](05-helper.md) — `generate_uploaded_file_name()` в
   `app/helpers.php`.
6. [06-filament-resource.md](06-filament-resource.md) — Filament-ресурс
   `MediaFileResource` со схемой, таблицей и страницами; регистрация
   навигационной группы `Медиа`.
6b. [06b-policy.md](06b-policy.md) — `MediaFilePolicy` (Admin bypass,
    Editor create/update, Viewer read-only).
7. [07-picker.md](07-picker.md) — `MediaPickerAction`, `MediaGrid`
   поле-компонент и Blade-шаблон.
8. [08-localization.md](08-localization.md) — ключи `panel.media_*` в
   `lang/ru/panel.php` и `lang/en/panel.php`.
9. [09-attach-to-models.md](09-attach-to-models.md) — трейт на
   Page/Post/Project/Footer/GlobalSetting; запуск `media:sync`;
   опционально — добавить `hintAction(MediaPickerAction::make(...))`
   в самые «горячие» `FileUpload`.
10. [10-tests.md](10-tests.md) — юнит-тесты сервиса, feature-тест
    команды, smoke-тест ресурса.

## Definition of done

- `php artisan test --filter=Media` зелёный.
- В `admin/media-files` работают: список, фильтры, редактирование,
  удаление (с физическим удалением файла), копирование URL.
- В любой модели с трейтом (сохранение через админку) появляются записи
  в `media_file_usages` с корректным `field`.
- Пикер (в `FileUpload` c hintAction) открывает грид, поиск/пагинация
  работают, выбор возвращает `path` в поле.
- `php artisan media:sync` проходит без ошибок на прод-снимке.
- Обновлены `.ai/file-map.md`, `.ai/domain.md`, `.ai/index.md`.
- Первичный прогон `vendor/bin/sail bin pint --dirty --format agent` без
  замечаний.

## Справка

- [_source-prompt.md](_source-prompt.md) — оригинальный промт целиком
  (с кодом всех классов). Читать при реализации конкретного шага
  вместе с соответствующим `NN-*.md`.
- [_verification.md](_verification.md) — итог глубокой сверки плана
  против vendor-кода Filament 4 / Laravel 12 и всей кодовой базы
  проекта. Проверенные API, инварианты, обоснование правок и «что
  специально не трогаем». Читать один раз перед началом реализации —
  чтобы не переспрашивать «а работает ли эта штука в v4».
