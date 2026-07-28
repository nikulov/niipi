# Итог глубокой сверки плана против кодовой базы и vendor/

Второй проход по коду — фиксация того, что реально проверено. Все API,
на которые опирается план, проверены по исходникам `vendor/filament/`
(v4.x) и `vendor/laravel/framework/` (v12).

## Filament 4 API — проверено в vendor/

- `Filament\Actions\Action` — используется в проекте
  (`app/Filament/Forms/Components/UrlInput.php:6`,
  `Posts/Tables/PostsTable.php:8` и др.). ✓
- `->hintAction(Action | Closure)` —
  `vendor/filament/forms/src/Components/Concerns/HasHint.php:124`.
  `Closure` вычисляется через `$this->evaluate($hintAction)` — совпадает с
  тем, что делает `MediaPickerAction::make()` (возвращает Closure). ✓
- `Action->schema([...])->action(function (array $data, Get $get, Set $set))`
  — `vendor/filament/actions/docs/02-modals.md:100-110` показывает
  ровно этот паттерн: `$data` = данные модалки, инжект утилит для
  родительского контекста. При `hintAction` на форменном поле `Set $set`
  пишет в родительскую форму — что и нужно для записи `path` в
  `FileUpload`. ✓
- `->modalWidth('7xl')` — принимает `Width | string | Closure | null`
  (`vendor/filament/actions/src/Concerns/CanOpenModal.php:317`); строка
  `'7xl'` совпадает с `Width::SevenExtraLarge->value`. ✓
- `Filament\Schemas\Components\Fieldset::make($label)` — принимает label
  напрямую, `->label(...)` перезаписывает. `->columns(24)` уже
  используется в проекте (`PostForm.php:33`). ✓
- `Section->collapsible()->collapsed(Closure)->hidden(Closure)` —
  все три из `vendor/filament/schemas/src/Components/Concerns/*`. ✓
- `Filament\Infolists\Components\TextEntry` — namespace корректен
  (`vendor/filament/infolists/src/Components/TextEntry.php:3`).
  `->markdown()`, `->copyable()`, `->badge()`, `->getStateUsing()` —
  все есть в `Concerns/CanFormatState.php`. ✓
- `TextColumn::make('usages_count')->counts('usages')` —
  `vendor/filament/tables/docs/02-columns/01-overview.md:118`. ✓
- `SelectFilter->options(MediaFileType::class)` — на вход принимает
  строку (enum-class);
  `vendor/filament/forms/src/Components/Concerns/HasOptions.php:24`
  автоматически распознаёт enum + `HasLabel`. ✓
- `TernaryFilter->queries(true: ..., false: ...)` +
  `FiltersLayout::AboveContent` + `deferFilters(false)` — существуют в
  `vendor/filament/tables/src/Filters/`. ✓
- `->recordActions([...])` — v4 имя метода (v3 было `->actions`);
  проект уже везде на v4 (`Categories/Tables/*.php` и др.). ✓
- Кастомное поле `MediaGrid extends Filament\Forms\Components\Field` —
  базовый класс `Field extends Component implements HasValidationRules`
  (`vendor/filament/forms/src/Components/Field.php:18`). ✓
- В Blade-вью: `$makeGetUtility()` — метод в
  `vendor/filament/schemas/src/Components/Concerns/HasState.php:723`.
  `$getFieldWrapperView()` — в
  `vendor/filament/schemas/src/Components/Concerns/HasFieldWrapper.php:18`.
  Обе доступны на кастомном Field. ✓
- `moveFiles()` на `FileUpload` —
  `vendor/filament/forms/src/Components/BaseFileUpload.php:333`. ✓
- Alpine `$tooltip` helper — идёт из `@ryangjchandler/alpine-tooltip`,
  подключается в `vendor/filament/support/resources/js/index.js`. ✓

## Laravel 12 — проверено

- `Blueprint::id()->startingValue(1001)` —
  `vendor/laravel/framework/src/Illuminate/Database/Schema/ColumnDefinition.php:30`
  + грамматика MySQL/Postgres. ✓
- `Model::getAttributes()` вызывает `mergeAttributesFromCachedCasts()`
  перед возвратом (`HasAttributes.php:1962-1967`) — гарантирует, что
  для array-cast'ов вернётся JSON-строка (то, что ожидает
  `MediaUsageService::extractPaths`). ✓
- Заявленный порядок событий: `bootTraits()` вызывается ДО `booted()`
  модели → трейт регистрирует `static::saved` первым, потом
  `Post::booted()` регистрирует второй. Оба срабатывают на `saved`,
  трейт сначала. Между ними нет гонки (пишут в разные таблицы). ✓

## Проектная специфика — проверено чтением моделей

- **Единственный класс в `app/Models/Concerns/`** — трейт
  `HasSectionOptions` (`app/Models/Concerns/HasSectionOptions.php`).
  Не регистрирует событий модели, чисто вспомогательные методы. С
  `TracksMediaUsage` компонуется чисто. ✓
- **`Menu.top_items` / `footer_items`** — из `Menu.php:36-73` видно:
  формат `type/url/label/page_slug/blank/children` без путей к файлам.
  Трейт не нужен. ✓
- **`Form.user_mail_attachments`** — JSON пути; файлы в
  `storage/app/public/forms/user-mail-attachments/` (см. на диске).
  Скрыто от media:sync правкой A в `04-artisan-command.md`. Форма
  осознанно вне трекинга. ✓
- **`Storage::disk('public')`** — есть в
  `config/filesystems.php:24-30`, `url` = `env('APP_URL').'/storage'`.
  `storage:link` сделан (иначе публичный сайт уже был бы битым). ✓
- **Финалы**: `Form`, `FormField`, `FormSubmission`, `FormSubmissionFile`
  — `final class`. Не наследуются никем и не мешают
  `class_uses_recursive`. ✓
- **Абстрактные модели**: нет. `MediaSyncCommand::getTrackedModelClasses`
  безопасно перебирает всё в `app/Models/*.php`. ✓
- **Morph map**: `Relation::morphMap` / `enforceMorphMap` в проекте
  **не используется** (`grep` в `app/`, `bootstrap/`, `config/` — пусто).
  Значит `usable_type` в `media_file_usages` будет = FQCN
  `App\Models\Post` и т.д. Если в будущем добавят `morphMap` — надо
  запустить `media:sync --usages-only` для перезаписи, но это уже
  общая проблема Laravel-morph, не наша.
- **`storage/app/public/` root-level мусор** (типа
  `5KEflU2ervweMd76vzNZrBBrdnTAnftI1TkgPelZ.jpg`): `media:sync` их
  проиндексирует, но `MediaUsageService::looksLikeFilePath()` требует
  `str_contains($value, '/')` — так что ни одна модель их не
  «отадаптирует» → `usages_count = 0` навсегда. Ожидаемое поведение
  (admin увидит их и сможет удалить).

## Тесты — проверено

- `CACHE_STORE=array` в `phpunit.xml`. Array-driver Laravel поддерживает
  теги (per-request). `cache()->tags([...])->flush()` и
  `Cache::forget(...)` — оба безопасны и не бросают исключений в тестах.
  Значит `MediaFile::deleted` cache-flush в тестах не упадёт. ✓
- Стиль ролевого тестирования уже задан в
  `tests/Feature/Filament/ResourceAccessTest.php` — HTTP через
  `actingAs`. Наш `MediaFileResourceTest` следует этому стилю. ✓
- `$this->userOfRole(UserRole::Admin|Editor|Viewer)` — уже есть в
  `tests/TestCase.php`, готовая инфраструктура. ✓

## Что специально НЕ трогаем

- **`ContentRenderer` cache** — закомментирован (`Services/ContentRenderer.php:12`).
  Не надо ни включать, ни трогать.
- **`Menu` model** — трейт не подключаем.
- **`Form` / `FormSubmission*`** — трейт не подключаем; папку `forms/`
  исключаем в `MediaSyncCommand` (правка A).
- **Существующие `FileUpload` closure'ы** — не заменяем на
  `generate_uploaded_file_name` массово (см. Boundaries в README).

## Третий проход — дополнительные находки

### Политики — строгая конвенция проекта

Проверено `ls app/Policies/` + `PostPolicy.php`: **каждая модель имеет
свою `Policy`**. Editor не может `delete` даже свои Post'ы. Viewer —
read-only. `BasePolicy::before()` даёт Admin bypass.

Без `MediaFilePolicy` Filament даёт всем аутентифицированным полный
CRUD → нарушает паттерн. **В план добавлен `06b-policy.md`** с матрицей
Admin=all / Editor=CRU / Viewer=R. Тесты на роли — в `10-tests.md`.

### Namespace `MediaFile` vs `Footer`

Обе модели в `App\Models\`. В `MediaFile::booted()` вызов
`Footer::cacheKey()` не требует `use App\Models\Footer;` — тот же
namespace. `02-model-enum-trait.md` подправлен, чтобы не советовать
лишний `use`.

### Локализация — какие ключи УЖЕ есть

`grep` в `lang/ru/panel.php`: `search`, `thumbnail`, `file_name`,
`mime_type`, `size`, `title`, `alt`, `file`, `settings`, `url`, `type`,
`edit`, `delete`, `created_at` — все ЕСТЬ. `media_*` — ни одного.
`08-localization.md` теперь явно перечисляет что НЕ трогать.

## Финальная оценка плана

Готов к реализации. Все правки против оригинального промта задокументированы:
- `README.md` — 5 ключевых решений + матрица ролей
- `02-model-enum-trait.md` — проектный `booted()` (cache-flush для
  Footer/GlobalSetting), без лишнего `use Footer`
- `04-artisan-command.md` — правки A/B/C (skip `forms/`, orderBy, robust Concerns)
- `06b-policy.md` — `MediaFilePolicy` с матрицей ролей
- `08-localization.md` — только `media_*` ключи, остальное уже есть
- `09-attach-to-models.md` — какие модели и почему; какие НЕ трогаем
- `10-tests.md` — тестовая модель, стиль ролевых тестов, проверки политики
