# План: баги и cleanup-задачи

Составлено после deep-review 2026-07-14 (после того, как P0/P1/P2/P3/P4
тестового покрытия закрыты). Компактный чек-лист статусов — в
[bugs-checklist.md](bugs-checklist.md).

## Легенда

- **P0** — активный баг, ломает пользовательский поток, чинить срочно.
- **P1** — активный баг, но узкий сценарий/редкий трафик.
- **P2** — латентный (сработает при определённых условиях), либо cleanup
  с осязаемой ценностью.
- **P3** — code smell / микро-cleanup.

---

## P0 — активные баги

### 1. `FormRulesBuilder::parseExtraRules` тихо роняет list-form правила

**Файл:** `app/Services/Forms/FormRulesBuilder.php:170`

**Симптом:** пользовательские правила валидации из
`FormField::$rules = ['min:3', 'email']` (list-форма JSON) не применяются
— поле не проверяется вовсе. Работает только assoc-форма
`['min:3' => 'message']`.

**Трасса:** `array_keys(['min:3']) = [0]`, `range(0, 0) = [0]`, `[0] !== [0]` →
`false` → `$isAssoc = false` → `return [[], $messages]`.

**Контекст:** админ-UI (`FieldsRelationManager.php:131`) — свободный JSON,
не запрещает list-форму → админ легко напишет её и ничего не заметит.

**Фикс (варианты):**

- В `parseExtraRules` для не-assoc возвращать `[array_values($rules), []]`
  (без пользовательских сообщений).
- Плюс: документировать в helper-text редактора, что assoc-форма
  привязывает сообщения к правилам.

**Тест:** есть `tests/Unit/Services/Forms/FormRulesBuilderTest.php` — сейчас
проверяет только assoc-форму (адаптирован под текущее поведение). После
фикса добавить кейс с list-формой.

---

### 2. `SendFormSubmissionEmails` не идемпотентен

**Файл:** `app/Jobs/SendFormSubmissionEmails.php:96`

**Симптом:** админ получает дубликаты писем при retry. Если admin-письмо
ушло, а user-письмо упало → catch → `status = Failed` → `throw` → job
повторяется до `tries = 5` раз.

**Трасса:** `handle()` не сверяется с текущим статусом submission и не
трекает, что admin-часть уже завершена.

**Фикс (варианты):**

- Хранить прогресс на submission: колонки `admin_sent_at`, `user_sent_at`;
  в начале handle пропускать уже отправленные плечи.
- Разбить на два отдельных job'а (admin и user) — независимые retry.
- Флаг `sent_to_admin` / `sent_to_user` в submission.

**Тест:** `tests/Unit/Jobs/SendFormSubmissionEmailsTest.php` — после фикса
добавить кейс двойного запуска handle().

---

### 3. `SubmitFormAction` оставляет осиротевшие файлы при откате транзакции

**Файлы:** `app/Actions/Forms/SubmitFormAction.php:57`,
`app/Services/Forms/SubmissionFilesStorer.php:45`

**Симптом:** `$upload->store()` пишет файл на диск **до** `FormSubmissionFile::create`;
если DB-транзакция откатится (deadlock, constraint), файл остаётся в
`storage/app/public/forms/{formId}/{submissionId}/` без строки в БД.

**Фикс (варианты):**

- Собрать список загруженных путей и удалить их в `catch` вокруг
  `DB::transaction`.
- Собирать `UploadedFile` и вызывать `store()` **после** успешного
  commit транзакции (но тогда submissionId для пути надо знать заранее).
- Периодическая cron-задача чистки orphan-файлов (не идеально).

---

## P1 — активные, но узкие

### 4. `HasSectionOptions::getSectionOption` возвращает null от первого пустого блока

**Файл:** `app/Models/Concerns/HasSectionOptions.php:29`

**Симптом:** если на странице **два** блока с одинаковым типом (например,
`bg-for-main-section`), а у первого нет ключа `bgForMainSection` в
`data`, метод вернёт `null` и до второго блока не дойдёт.

**Трасса:**

```php
foreach ($blocks as $block) {
    if (($block['type'] ?? null) !== $blockType) continue;
    return $block['data'][$key] ?? null;  // ← первый матч, даже если null
}
```

**Фикс:** пропускать первый матч, если `data.$key` пустой; или
явно ограничить блоки-настройки одним экземпляром на страницу через
Filament (`->maxItems(1)`).

---

### 5. `SubmissionsStats` «Новых сегодня» не фильтрует по статусу

**Файл:** `app/Filament/Widgets/SubmissionsStats.php:22`

**Симптом:** счётчик показывает **все** отправки за день (включая
`Sent`, `Failed`, `Processing`), хотя лейбл «Новые сегодня» и соседние
счётчики фильтруют по статусу.

**Фикс:** добавить `->where('status', FormSubmissionStatus::New->value)`.

---

### 6. `SubmitFormAction::handle` — update статуса вне транзакции

**Файл:** `app/Actions/Forms/SubmitFormAction.php:67`

**Симптом:** `$submission->update(['status' => Processing])` идёт **после**
`DB::transaction`. Если update упадёт (сетевой сбой БД в момент между
commit и update), submission останется в статусе `New` с уже
загруженными файлами и НЕбез диспатча job'а.

**Фикс:** переместить update внутрь транзакции, либо ставить `Processing`
сразу при создании (в `SubmissionCreator::create`), а не переводить
после.

---

### 15. ~~Счётчики категорий в `NewsFull`/`ProjectsFull` включают будущие публикации~~ ✅ исправлено

**Файлы:**
- `app/Livewire/Components/NewsFull.php:27-30` (`posts as posts_count`)
- `app/Livewire/Components/ProjectsFull.php:27-30` (`projects as projects_count`)
- `app/Livewire/Components/AbstractContentFull.php:127-134` (`getTotalCount` — счётчик «Все»)

**Симптом:** после фикса 91c28d2 (`NewsQuery`/`ProjectsQuery` → `->published()`)
выборка карточек фильтрует посты/проекты по `published_at <= now()`, а
счётчики — нет. Расхождение: «Строительство (5)», клик → показывает 3
(два будущих не рендерятся, но всё ещё учтены в счётчике). Аналогично
для tab «Все».

**Трасса:**
```php
// NewsFull.php
->withCount([
    'posts as posts_count' => fn ($q) =>
    $q->where('status', PostStatus::Published->value),
]);
// без ->where('published_at', '<=', now())
```

**Фикс:**
- В обоих `buildCategoriesQuery` — добавить `->where('published_at', '<=', now())`
  внутрь замыкания `withCount`.
- В `AbstractContentFull::getTotalCount` — добавить `->where($contentTable.'.published_at', '<=', now())`
  после фильтра по статусу.
- Тесты: `tests/Feature/Livewire/NewsFullTest.php`, `ProjectsFullTest.php` — добавить
  кейс «пост с будущим `published_at` не учитывается в счётчике».

**Почему P1, не P0:** визуальное расхождение цифр в списке категорий.
Не ломает функциональность, но выглядит как баг.

---

## P2 — латентные / низкий приоритет

### 7. Type-hint `Post $post` в forceDelete/forceDeleteAny (11 полиси)

**Файлы:** `CategoryPolicy.php:37`, `FooterPolicy.php:37`,
`FormFieldPolicy.php:37`, `FormPolicy.php:37`, `FormSubmissionFilePolicy.php:37`,
`FormSubmissionPolicy.php:37`, `GlobalSettingPolicy.php:37`,
`MenuPolicy.php:37`, `PagePolicy.php:37`, `ProjectPolicy.php:37`,
`UserPolicy.php:37` (`ProjectPolicy` ещё и в `view/update` использует
`Project $post`, что валидно, но криво по именованию).

**Симптом:** для Editor/Viewer (перед `before()` = null) вызов
`Gate::forUser($editor)->allows('forceDelete', $category)` даёт
`TypeError` — сигнатура ждёт `Post`, а прилетает `Category`.

**Почему не активно сейчас:** `grep 'use SoftDeletes' app/Models/`
пусто → Filament не показывает force-delete UI. TypeError достижим
только если кто-то добавит SoftDeletes или вручную вызовет Gate.

**Фикс:** заменить `Post $post` на соответствующий тип модели во всех
полиси. Быстрый механический патч.

---

### 8. `FormRulesBuilder::filterMimesRules` слишком агрессивен

**Файл:** `app/Services/Forms/FormRulesBuilder.php:148`

**Симптом:** отбрасывает и `mimes:*`, и `mimetypes:*` из extras. Это
разные проверки Laravel (расширение vs MIME-тип), они должны
сосуществовать.

**Почему не активно:** редактор `rules` для полей типа `file` **скрыт**
в UI (`FieldsRelationManager.php:134`), так что admin не может добавить
`mimes:pdf` через интерфейс.

**Фикс:** фильтровать только `mimetypes:` (дедуп с авто-правилом из
`accept_mimes`), `mimes:` пропускать.

---

### 9. `AbstractContentFull::mount` не типизирует `categoryIds`

**Файл:** `app/Livewire/Components/AbstractContentFull.php:37`

**Симптом:** `array_values` сохраняет исходные типы. `NewsQuery::list` /
`ProjectsQuery::list` использует массив в `whereIn` как есть — если
там строки/`null`, MySQL коэрсит, но неаккуратно.

**Почему не активно:** Filament Select хранит int-ключи опций, так что
в проекте всегда int'ы приходят. Латентно.

**Фикс:** привести к `int[]` и выбросить не-int/не-positive в mount
(таким же образом, как это делает `getCategories()`).

---

## P3 — cleanup

### 10. Пустой try/catch в `PublicForm::submit`

**Файл:** `app/Livewire/Forms/PublicForm.php:99`

```php
try {
    $action->handle(...);
    ...
} catch (ValidationException $e) {
    throw $e;
}
```

`catch { throw }` — no-op. Убрать блок целиком, оставить голый вызов.

---

### 11. Опечатка `AuthServiceProvoider` + мёртвый `$policies`

**Файл:** `app/Providers/AuthServiceProvoider.php`

Класс и файл названы с опечаткой (`Provoider` вместо `Provider`).
Массив `$policies` не используется — Laravel 12 подхватывает политики
по конвенции. Задокументировано в `.ai/decisions.md`.

**Фикс:** либо переименовать (изменить `bootstrap/providers.php`), либо
удалить файл целиком, если провайдер действительно пустой.

---

### 12. `ProjectObserver` параметр называется `$post`

**Файл:** `app/Observers/ProjectObserver.php:10`

`public function saving(Project $post)` — переменная `$post` для модели
`Project`. Копипаста из `PostObserver`. Переименовать в `$project`.

---

### 13. `CategoryStatus::Published = 'active'`

**Файл:** `app/Enums/CategoryStatus.php:10`

Значение отличается от `Post/Page/ProjectStatus::Published = 'published'`
— ловушка для общих хелперов и сравнений строкой. Либо выровнять
на `'published'` (миграция данных), либо задокументировать в
`.ai/domain.md`.

---

### 14. `FormEmailTemplateRenderer` — коллизия имён data/file полей

**Файл:** `app/Services/Forms/FormEmailTemplateRenderer.php:78`

Если текстовое поле и file-поле называются одинаково, URL файлов
перезаписывает пользовательский ввод в `$field[$fieldName]`. Практически
недостижимо (имена полей уникальны), но не защищено кодом.

**Фикс:** валидатор уникальности `name` на уровне `FormField::create`,
либо ключи для файлов вынести в отдельный неймспейс (`fileUrl[]`).

---

## Definition of done секции

- P0 (пункты 1–3) закрыты, добавлены регресс-тесты.
- P1 (пункты 4–6) закрыты или сознательно отложены с записью в
  `.ai/decisions.md`.
- P2/P3 — по приоритету.
- Чек-лист `bugs-checklist.md` синхронен с этим файлом.
