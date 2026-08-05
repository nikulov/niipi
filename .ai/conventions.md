# Соглашения

## Стиль кода PHP

- **Форматтер:** Laravel Pint. Запуск: `vendor/bin/sail bin pint --dirty` после правки PHP.
  `--format` принимает только `txt` (дефолт), `json`, `checkstyle`, `junit`, `gitlab`;
  `agent` и `github` не поддерживаются (pint 1.25) — падает с «Format [...] is not supported».
  Проверка без правки файлов — `--test`.
- **PSR-4:** `App\` → `app/`, `Database\Factories\` → `database/factories/`,
  `Database\Seeders\` → `database/seeders/`, `Tests\` → `tests/`.

## Frontend

- **Форматтер:** Prettier + `prettier-plugin-blade` + `prettier-plugin-tailwindcss`.
  Запуск: `npm run format` (или через Sail).
- **Tailwind 4** — utility-first. Классы упорядочивает prettier-plugin-tailwindcss.
- Alpine.js — маленькие интерактивы; крупные — Livewire-компоненты.

## Именование

- **Модели:** ед. число, PascalCase (`Post`, `Project`).
- **Enum:** `{Сущность}{Свойство}` (`PostStatus`, `CategoryType`).
- **Filament Resources:** `{Модель}Resource` в
  `app/Filament/Resources/{Модели}/` со стандартными подпапками
  (`Pages/`, `Schemas/`, `Tables/`, при нужде `RelationManagers/`).
- **Блоки:** `{Название}Renderer` в `app/Blocks/Renderers/`.
- **Livewire:** `App\Livewire\Components\{Имя}` для UI-фрагментов,
  `App\Livewire\Forms\{Имя}` для форм.
- **Services:** осмысленное существительное/глагол-существительное
  (`ContentRenderer`, `NewsQuery`).

## Блочный контент

- Новый тип блока = класс в `app/Blocks/Renderers/` + регистрация в
  `App\Blocks\BlockRenderRegistry` + запись в
  `app/Filament/Components/BlockRegistry/BlockRegistry.php` для админки.
- Шаблоны — Blade в `resources/views/components/sections/` или соответствующем
  подкаталоге.
- **Блок-настройка** (не рендерится, а задаёт параметр секции — тип из
  `HasSectionOptions::sectionOptionBlockTypes()`, сейчас
  `bg-for-main-section`) регистрируется с `->maxItems(1)` и **только в той
  секции, которую настраивает**. Причина: `getSectionOption()` читает
  первый блок нужного типа и дальше не идёт, а `getBlocksForSection()`
  видит только верхний уровень JSON-колонки — экземпляр во вложенном
  билдере (`tabs()`, `modal()`) не прочитается никогда. `maxItems`
  фильтрует пикер, но не валидирует сохранение и не чинит уже
  сохранённые записи.

## Alt у картинок

- Каждый публичный `<img>` должен получать осмысленный `alt`. Пустой
  `alt=""` в шаблонах не оставлять.
- Порядок фоллбека (от конкретного к общему):
  1. Ручное поле из Filament (например `imageAlt`, `iconAlt`, `alt`).
  2. Контекст компонента: `cardTitle`, `$card['title']`, `pageTitle`
     (пробрасывать из презентера/рендерера).
  3. Последний рубеж — `config('app.name')`.
- Для галерей (много картинок без ручных alt) — шаблон
  `«{pageTitle}» — фото {N}` (см. `gallery.blade.php`).
- `background-image` (CSS) alt не требует и не поддерживает — считается
  декоративным. Если фон реально несёт смысл — заменять на `<img>` или
  добавлять `role="img" aria-label="…"` на элемент-контейнер.

## Пути к файлам

- `public_asset()` отдаёт **URL для браузера** (абсолютный, через
  `Storage::disk('public')->url()`). Читать по нему файл на сервере нельзя:
  `file_get_contents(public_asset(...))` — это HTTP-запрос сервера к самому
  себе через DNS, он валится на каждом моргании резолвера (баг #26).
- Содержимое читать локально: `Storage::disk('public')->get()/->path()` или
  `resource_path()`. Для инлайна SVG есть `inline_svg()` в `app/helpers.php`
  (резолвит `resource_path()` → диск `public`, отдаёт `''` если не нашёл).
- Путь из админки приходит строкой в JSON-колонке репитера, а не из
  `FileUpload`. Ни `resource_path()`, ни `Storage::path()` не нормализуют
  `..` — перед чтением проверять путь на traversal и на ожидаемое
  расширение, иначе `../.env` уедет в разметку страницы.

## Формы

- Публичные — Livewire-компонент `PublicForm` под управлением модели `Form`.
- Отправка — `App\Actions\Forms\SubmitFormAction` (rate-limit 5/300с/IP,
  внутри валидация через `FormRulesBuilder`, файлы через `SubmissionFilesStorer`,
  email через асинхронный `SendFormSubmissionEmails` job).
- Файлы вложений — модель `FormSubmissionFile`.
- Специализированные сервисы — `app/Services/Forms/` (по одному классу
  на ответственность, оркестратор — Action).
- Поле `phone` — клиентская маска `+7 (452) 354 32 53` (Alpine-компонент
  `phoneMask` в `resources/js/app.js`) + серверный `regex` в `FormRulesBuilder`;
  в БД уходит `+74523543253` (`SubmissionDataNormalizer`).
  Подробности — [patterns/livewire-public-form.md](patterns/livewire-public-form.md).

## Livewire: публичные свойства

В компонентах, доступных анониму, **всё, что компонент строит сам, помечать
`#[Locked]`** — открытыми оставлять только свойства на `wire:model`. Чексумма
Livewire покрывает снапшот, но не карту `updates`, поэтому незалоченное
публичное свойство переписывается запросом клиента.

Образец — `App\Livewire\Forms\PublicForm`: залочены `$form`, `$viewData`,
`$submitted`, `$componentKey`; открыты `$data`, `$uploads`, `$website`.

Механика и разбор реального падения —
[manual/livewire-3.md](manual/livewire-3.md).

## Авторизация

- Видимость ресурса в админке — trait `App\Filament\Support\RoleAccessResource`
    - `allowedRoles()`.
- Права на действия — политики `App\Policies\*Policy` наследуют `BasePolicy`
  (Admin bypass через `before()`).
- Политики автоподхватываются по конвенции `App\Policies\{Model}Policy`.
  `AuthServiceProvider` в проекте нет — он не нужен и заводить его для
  регистрации политик не надо (удалён 2026-08-04 вместе с мёртвым
  массивом `$policies`).

## Presenter / Composer

- Тяжёлые данные для view выносятся в `App\Presenters\{Blocks|Forms}\*` —
  плоские массивы или объекты для Blade.
- Данные, шаримые в много шаблонов — через `App\View\Composers\*` в
  `AppServiceProvider::boot()` (`View::composer(...)`).

## Копирование сущности

- Модели с action «Копировать» подключают трейт `App\Models\Concerns\Duplicatable`.
  Обязательно переопределяют `prepareDuplicate(Model $copy)` (сброс
  статуса/активности). Опционально — `copyRelationsTo(Model $copy)` для
  HasMany/BelongsToMany. Модели без пары `title` + `slug` переопределяют
  `duplicateTitleColumn()` и `duplicateSlugColumn()` (Form → `name` / `null`).
- Логика суффиксов (`(копия N)` / `-copy-N`) и `nextCopyNumber` — в
  `App\Services\ModelDuplicator`. Всё в `DB::transaction`.
- Filament row action — `App\Filament\Actions\CopyAction`
  (`getDefaultName='copy'`, чтобы не пересекаться со встроенным
  `Filament\Actions\ReplicateAction`). Авторизуется через
  `Gate::allows('create', $record::class)` — Filament v4 авто-авторизует
  только built-in actions, кастомные надо гейтить руками.

## Кэш

- Драйвер должен поддерживать теги (Valkey). Использование:
  `cache()->tags([...])->remember(key, ttl, fn)`.
- Модели контента флашат теги в `booted()` (см.
  [patterns/cache-flush-on-save.md](patterns/cache-flush-on-save.md)).
- Single-row сущности (`Footer`, `GlobalSetting`) — `Cache::rememberForever`
    - `Cache::forget` в `saved`/`deleted`.
- Содержимое файлов вечно по ключу-пути **не кэшировать**: у `FileUpload`
  стоит `preserveFilenames()`, перезалитый файл сохраняет путь, и кэш отдаёт
  старую версию, пока не сделаешь `cache:clear`. Промах (файла нет в момент
  первого рендера — например, шара ещё не примонтирована на деплое) осядет
  так же навсегда. Если кэш нужен — ключ с `filemtime()`, промахи не писать.

## Миграции

- Именование Laravel-стандарт: `YYYY_MM_DD_HHMMSS_action_on_table.php`.
- Изменения существующих таблиц оформлять отдельными миграциями
  (`add_x_to_y_table`, `change_x_type_on_y_table`, `drop_x_from_y_table`).

## Тесты

- PHPUnit 11 (не Pest, судя по `phpunit.xml`).
- `tests/Unit/` — юниты; `tests/Feature/` — фичи (в т.ч. HTTP).
- Тестовая БД: `DB_DATABASE=testing` (см. `phpunit.xml`).

## Прочее

- Cookie `cookie_consent` не шифруется (см. `bootstrap/app.php`).
- Хелперы приложения — `app/helpers.php`, подключается в `bootstrap/app.php`.
- **Никогда не удалять существующие тесты без явного разрешения.**
