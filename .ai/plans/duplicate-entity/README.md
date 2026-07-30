# Копирование сущности (Duplicate row action)

## Задача

Добавить универсальное действие «Копировать» в строке таблицы Filament-ресурса
для Post, Project, Page, Form. Клик по иконке → confirmation-модал → в
транзакции создаётся полная копия записи с уникальными «(копия)»-суффиксами
на ключевых текстовых полях, статус/активность сбрасывается, дочерние
отношения копируются.

Промт-донор — из другого проекта (Laravel 12 + Filament v5), адаптирован
под наш стек (Filament v4, RU-панель, свои модели) в чате 2026-07-29.

## Ключевые решения и контекст

- **Filament v4, не v5.** API `Filament\Actions\Action`, `recordActions([...])`,
  `Heroicon::DocumentDuplicate` — совместимы с промтом как есть.
- **Ресурсы:** Post, Project, Page, Form. Category не включаем (простая
  сущность, ценность копирования сомнительна).
- **Обобщение под две «форматные» колонки** (paren-format для видимого
  имени + dash-format для URL-slug):
  - Сервис держит два формата в константах: `TITLE_WORD = 'копия'`
    (используется в `(копия)` / `(копия N)`) и `SLUG_WORD = 'copy'`
    (используется в `-copy` / `-copy-N`).
  - Модель через трейт объявляет **две колонки**:
    - `duplicateTitleColumn(): string` — колонка paren-формата
      (visible name). Дефолт — `'title'`. Form переопределяет на `'name'`.
    - `duplicateSlugColumn(): ?string` — колонка dash-формата (URL slug),
      либо `null`. Дефолт — `'slug'`. Form переопределяет на `null`.
- **`nextCopyNumber`** сканирует title-колонку И (если задана) slug-колонку,
  берёт `max` по обеим — так title и slug остаются в лок-стэпе даже если
  одну из копий вручную переименовали.
- **Хук `patchDuplicatedContent` (JSON-патч self-id) — НЕ добавляем.** В
  Post/Page/Project блоки самодостаточны, self-id внутри JSON нет. Появится
  такой блок — вернём хук.
- **`TracksMediaUsage` сам подхватит usages для копии** на `saved` — доп.
  кода не нужно. Полностью снятые usages оригинала не пересчитываются
  (они принадлежат оригиналу).
- **Observers (`PostObserver` и т.д.)** ставят `published_at = now()` только
  при `status = Published`. `prepareDuplicate` сбрасывает в Draft — observer
  не вмешается.
- **`Page::setSlugAttribute`** strip'ает leading `/` — совместимо с нашим
  присвоением `$copy->slug = "…-copy"`.
- **Filament: имя экшена = `copy`.** ⚠️ В Filament v4 есть встроенный
  `Filament\Actions\ReplicateAction` c `getDefaultName() = 'replicate'`.
  Чтобы не пересекаться — наш экшен использует имя `copy`. Иконка
  `Heroicon::DocumentDuplicate`, цвет `gray`, без label — только tooltip.
- **Почему не использовать встроенный `ReplicateAction`?** Он не оборачивает
  `save()` в транзакцию, разделяет логику на `beforeReplicaSaved` +
  `after`, и не умеет считать «(копия N)»-суффиксы. Свой `ModelDuplicator`
  оставляет всю логику в одной транзакции и тестируется без Filament.

## Файлы

- **NEW** `app/Services/ModelDuplicator.php`
- **NEW** `app/Models/Concerns/Duplicatable.php` (трейт)
- **NEW** `app/Filament/Actions/CopyAction.php`
- **EDIT** `app/Models/Post.php` — use `Duplicatable`, `prepareDuplicate`,
  `copyRelationsTo` (categories pivot)
- **EDIT** `app/Models/Project.php` — то же (categories pivot)
- **EDIT** `app/Models/Page.php` — use `Duplicatable`, `prepareDuplicate`
  (без copyRelationsTo — пивотов нет)
- **EDIT** `app/Models/Form.php` — use `Duplicatable`, override
  `duplicateTitleColumn() = 'name'` + `duplicateSlugColumn() = null`,
  `prepareDuplicate` (`is_active=false`), `copyRelationsTo` (fields
  HasMany, БЕЗ submissions)
- **EDIT** `app/Filament/Resources/Posts/Tables/PostsTable.php` — add
  `CopyAction` между Edit и Delete
- **EDIT** `app/Filament/Resources/Projects/Tables/ProjectsTable.php` — то же
- **EDIT** `app/Filament/Resources/Pages/Tables/PagesTable.php` — то же
- **EDIT** `app/Filament/Resources/Forms/Tables/FormsTable.php` — добавить
  `CopyAction` (сейчас там только `EditAction`, `DeleteAction` нет —
  добавляем `CopyAction` после `EditAction`)
- **EDIT** `lang/ru/panel.php` + `lang/en/panel.php` — ключи `copy`,
  `copy_post`, `copy_project`, `copy_page`, `copy_form`, соответствующие
  `_confirm` и `*_copied`

## Order of work

1. **Сервис + трейт.**
    - `app/Services/ModelDuplicator.php` — метод
      `duplicate(Model $model): Model` в `DB::transaction`. Константы
      `TITLE_WORD = 'копия'`, `SLUG_WORD = 'copy'`. Внутри:
      1. `$titleCol = $model->duplicateTitleColumn()`,
         `$slugCol = $model->duplicateSlugColumn()`.
      2. Парсинг: `parseTitleSuffix($model->{$titleCol})`; если
         `$slugCol !== null` — `parseSlugSuffix($model->{$slugCol})`.
      3. `nextCopyNumber` — сканирует `$titleCol` (и `$slugCol`, если есть)
         в БД, `max` по обоим.
      4. `$except = $slugCol !== null ? [$slugCol] : []` — исключаем slug
         из `replicate`, чтобы не поймать unique.
      5. `$copy = $model->replicate($except);`
      6. `$copy->{$titleCol} = makeTitle($baseTitle, $n);`
         если `$slugCol !== null` — `$copy->{$slugCol} = makeSlug(...);`
      7. `$model->prepareDuplicate($copy);`
      8. `$copy->save();`
      9. `$model->copyRelationsTo($copy);`
      10. `return $copy;`.
    - `app/Models/Concerns/Duplicatable.php`:
      ```php
      public function duplicate(): static {
          return app(ModelDuplicator::class)->duplicate($this);
      }
      public function duplicateTitleColumn(): string { return 'title'; }
      public function duplicateSlugColumn(): ?string { return 'slug'; }
      abstract public function prepareDuplicate(Model $copy): void;
      public function copyRelationsTo(Model $copy): void { /* no-op */ }
      ```
2. **Filament-действие.**
    - `app/Filament/Actions/CopyAction.php` — `getDefaultName() = 'copy'`
      (НЕ `replicate` — в v4 имя занято встроенным `ReplicateAction`),
      `label('')`, `icon(Heroicon::DocumentDuplicate)`, `iconSize('md')`,
      `color('gray')`, `requiresConfirmation()`, `modalSubmitActionLabel`
      = `__('panel.copy')`, `action(fn (Model $r) => $r->duplicate())`.
3. **Модели.**
    - `Post`: `use Duplicatable`, `prepareDuplicate` → status=Draft,
      published_at=null; `copyRelationsTo` → `attach(categories.pluck('id'))`.
    - `Project`: то же (categories через `category_project`).
    - `Page`: `use Duplicatable`, `prepareDuplicate` → status=Draft,
      published_at=null; `copyRelationsTo` — не переопределяем.
    - `Form`: `use Duplicatable`, `duplicateTitleColumn() = 'name'`,
      `duplicateSlugColumn() = null`, `prepareDuplicate` → `is_active
      = false`; `copyRelationsTo` → клон `fields`
      (`$this->loadMissing('fields'); foreach ($this->fields as $f) {
      $n = $f->replicate(); $n->form_id = $copy->id; $n->save(); }`).
4. **Таблицы.**
    - Posts/Projects/Pages: вставить `CopyAction::make()` между Edit и
      Delete с `tooltip`, `modalHeading` (fn record + record->title),
      `successNotificationTitle`.
    - Forms: вставить `CopyAction::make()` после `EditAction`
      (fn record + record->name).
5. **Переводы.** `lang/ru/panel.php` и `lang/en/panel.php`:
   ```
   'copy' => 'Копировать' / 'Copy',
   'copy_post' / 'copy_project' / 'copy_page' / 'copy_form' — tooltip
   'copy_*_confirm' — modalHeading с ':title'/':name'
   '*_copied' — success toast
   ```
6. **Тесты (Unit).** Кладём в `tests/Unit/Models/Concerns/` рядом с
   `HasSectionOptionsTest.php` (проектная конвенция для тестов трейтов).
   Фабрик у Post/Project/Page/Form/Category нет — создаём напрямую
   через `Model::create([...])` как в существующих тестах (см.
   `tests/Unit/Models/PostTest.php`, `tests/Unit/Models/FormTest.php`).
    - `DuplicatablePostTest.php` — «(копия)» + Draft + `published_at=null` +
      `categories` pivot клонирован. Повторный `duplicate()` → «(копия 2)» +
      `-copy-2`. Копия копии → «(копия 3)» + `-copy-3`.
    - `DuplicatableProjectTest.php` — аналогично Post.
    - `DuplicatablePageTest.php` — «(копия)»/Draft, без пивотов.
    - `DuplicatableFormTest.php` — «Имя (копия)» для `name`, `is_active =
      false`, `fields` клонированы (n штук, `form_id` новой), `submissions`
      НЕ клонированы (создаём одну через `Form::create → $form->submissions()->create(...)`,
      проверяем `$copy->submissions()->count() === 0`).
7. **Обновить `.ai/`.**
    - `file-map.md` — `Services/ModelDuplicator.php`,
      `Models/Concerns/Duplicatable.php`, `Filament/Actions/CopyAction.php`.
    - `conventions.md` — короткая заметка: «модели с копированием —
      трейт `Duplicatable`, префикс через сервис».
    - `patterns/` — при желании добавить `duplicatable-model.md`
      (по объёму паттерна, если решим что стоит вынести).

## Boundaries — что НЕ входит

- Копирование Category / User / Menu / Footer / GlobalSetting.
- Bulk-действие «скопировать выделенные».
- Копирование `FormSubmission`, `FormSubmissionFile`.
- Копирование медиа-файлов (physical files) — только сохранение путей,
  которое `TracksMediaUsage` подхватит автоматически. Физические файлы
  остаются общими на диске (для нашего use-case — норма).
- JSON-патч self-id (`patchDuplicatedContent`).
- Soft-delete-aware копирование.

## Gotchas

- **Unique slug**: у `posts`, `projects`, `pages` `slug` — unique. Сервис
  строит `$except = [$slugCol]` только если `$slugCol !== null`. Форма
  slug'а не имеет — `$except = []`.
- **`title` НЕ исключаем из replicate**: он не unique, а перезаписывается
  сразу после `replicate()` следующей строкой. Исключать не нужно.
- **`form_fields.unique(['form_id', 'name'])`**: при клонировании поля
  `form_id` меняется на id копии — коллизии `(form_id, name)` нет.
- **`withCount` / `withAggregate` попадают в `$attributes`** (например
  `FormsTable::modifyQueryUsing` добавляет `withCount(['fields',
  'submissions'])`). `Model::replicate()` без защиты копирует эти
  виртуальные атрибуты и `save()` падает `SQLSTATE 42S22 Unknown column
  fields_count`. Сервис фильтрует через
  `getSchemaBuilder()->getColumnListing($table)` — оставляет только
  реальные колонки. Инцидент подтверждён 2026-07-29 на форме.
- **Filament v4 авто-авторизует ТОЛЬКО built-in actions** (CreateAction,
  EditAction, DeleteAction, ReplicateAction, ViewAction, …) через
  `Page::getDefaultActionAuthorizationResponse()` по `instanceof`. Наш
  `CopyAction extends Action` под маппинг не попадает, `isAuthorized()`
  возвращает `true` по умолчанию → кнопка видна всем, у кого есть
  `canViewAny`. **Фикс**: в `setUp()` явно
  `->authorize(fn (Model $record) => Gate::allows('create', $record::class))`.
  Копирование семантически = создание новой записи, поэтому маппим на
  `create` ability. С фиксом:
  - Post/Project (create=Editor) → Editor+Admin видят.
  - Page/Form (create=false, только Admin через before) → только Admin.
- **`Page::setSlugAttribute`** обрезает `/` — при `$copy->slug =
  "about-copy"` mutator ничего не портит. Проверить не нужно, но помним.
- **`applicant_type` cast enum** у Form — `replicate()` копирует
  «сырое» значение, mutator при set вернёт enum. Всё штатно.
- **Порядок иконок** в `recordActions` для Post/Project/Page: Edit →
  Copy → Delete. Для Form: Edit → Copy (Delete нет в текущей таблице).
- **`TracksMediaUsage`** на копии сработает автоматически через `saved`;
  usages сгенерируются для нового `usable_id`. Оригинал не тронется.
- **`Model::replicate()` вызывает `setRelations($this->relations)`** —
  копия делит с оригиналом *in-memory*-коллекции загруженных отношений.
  `$copy->categories()->attach([...])` работает через relation query
  (в БД), поэтому корректно создаёт новые pivot-строки. In-memory
  свойство `$copy->categories` при этом остаётся stale — если после
  save понадобится обход, вызвать `$copy->refresh()` (нам не нужно).
- **PostObserver / PageObserver / ProjectObserver.saving**: ставит
  `published_at` только если `status === Published`. `prepareDuplicate`
  ставит Draft — observer не вмешивается. Проверить в тестах, что
  `published_at` на копии остался `null`.
- **`Cache::tags(['sitemap'])` и `['news','categories']` /
  `['projects','categories']` флашится** через `booted()`
  Post/Project/Page на `saved`. Копия дёрнет флаш — норма.
- **Копия «на главной» странице (`slug='home'`).** У Page есть особый
  slug `home`. После копии он становится `home-copy` — уникальность не
  нарушается, `ContentController::normalizePageSlug` продолжит работать
  для оригинала.

## Проверка

1. Открыть админку → таблицу Posts (Projects, Pages, Forms) → у строк
   появилась иконка копирования.
2. Клик → модал «Скопировать «имя»?» → «Копировать».
3. Проверить копию Post:
    - `title` = `Оригинал (копия)`
    - `slug` = `original-copy`
    - `status` = Draft
    - `published_at` = null
    - `categories` — те же, но через новый pivot-row
4. Копия Form:
    - `name` = `Имя (копия)`
    - `is_active` = false
    - `title` — как у оригинала (не суффиксируется, `slug` не меняется — его нет)
    - `fields.count()` = как у оригинала, у каждого `form_id` = id копии
    - `submissions.count()` = 0
5. Копия копии → «(копия 2)» + `-copy-2`.
6. Копия оригинала после «(копия 2)» → «(копия 3)» + `-copy-3` (max по
   всей таблице по обоим полям).
7. Ручное переименование `foo (копия)` в `foo bar` (slug остался
   `foo-copy`) → следующая копия → «(копия 2)» + `-copy-2` (по slug-max).
8. `vendor/bin/sail artisan test --compact` — регрессий нет.

## Checklist

- [x] `app/Services/ModelDuplicator.php`
- [x] `app/Models/Concerns/Duplicatable.php`
- [x] `app/Filament/Actions/CopyAction.php`
- [x] Post: `prepareDuplicate`, `copyRelationsTo`
- [x] Project: `prepareDuplicate`, `copyRelationsTo`
- [x] Page: `prepareDuplicate`
- [x] Form: `duplicateTitleColumn` + `duplicateSlugColumn`, `prepareDuplicate`, `copyRelationsTo`
- [x] PostsTable / ProjectsTable / PagesTable / FormsTable — `CopyAction`
- [x] `lang/ru/panel.php` + `lang/en/panel.php` — ключи
- [x] `tests/Unit/Models/Concerns/DuplicatablePostTest.php` /
  `…ProjectTest.php` / `…PageTest.php` / `…FormTest.php`
- [x] `vendor/bin/sail bin pint --dirty --format agent`
- [x] `vendor/bin/sail artisan test --compact`
- [x] `.ai/file-map.md` + `.ai/conventions.md` — обновить
