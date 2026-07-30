# Соглашения

## Стиль кода PHP

- **Форматтер:** Laravel Pint. Запуск: `vendor/bin/sail bin pint --dirty --format agent` после правки PHP.
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

## Формы

- Публичные — Livewire-компонент `PublicForm` под управлением модели `Form`.
- Отправка — `App\Actions\Forms\SubmitFormAction` (rate-limit 5/300с/IP,
  внутри валидация через `FormRulesBuilder`, файлы через `SubmissionFilesStorer`,
  email через асинхронный `SendFormSubmissionEmails` job).
- Файлы вложений — модель `FormSubmissionFile`.
- Специализированные сервисы — `app/Services/Forms/` (по одному классу
  на ответственность, оркестратор — Action).

## Авторизация

- Видимость ресурса в админке — trait `App\Filament\Support\RoleAccessResource`
    - `allowedRoles()`.
- Права на действия — политики `App\Policies\*Policy` наследуют `BasePolicy`
  (Admin bypass через `before()`).
- Политики автоподхватываются по конвенции `App\Policies\{Model}Policy`;
  массив `$policies` в `AuthServiceProvoider` — легаси, не работает.

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
