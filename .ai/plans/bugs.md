# План: баги и cleanup-задачи

Составлено после deep-review 2026-07-14 (после того, как P0/P1/P2/P3/P4
тестового покрытия закрыты). Компактный чек-лист статусов — в
[bugs-checklist.md](bugs-checklist.md).

Здесь только **открытое**. Закрытые пункты (#1, #3–#6, #15, #17–#21) —
в [archived/bugs.md](archived/bugs.md); нумерация сквозная, номера не
переиспользуются.

## Легенда

- **P0** — активный баг, ломает пользовательский поток, чинить срочно.
- **P1** — активный баг, но узкий сценарий/редкий трафик.
- **P2** — латентный (сработает при определённых условиях), либо cleanup
  с осязаемой ценностью.
- **P3** — code smell / микро-cleanup.

---

## P0 — активные баги

### 2. `SendFormSubmissionEmails` не идемпотентен

**Файл:** `app/Jobs/SendFormSubmissionEmails.php:96`

**Симптом:** три последствия, не одно (разобрано 2026-08-04).

1. **Дубли админу.** Падение любого плеча → `Failed` → `throw` → каждая
   новая попытка начинает с админского плеча заново. `tries = 5`,
   `backoff = [60, 300, 900]` + `last($backoff)` для попыток сверх массива
   → до 5 одинаковых писем за ~36 минут.
2. **Плечи связаны.** Оба `Mail::to()` в одном `try`, админское первым:
   его падение не даёт дойти до пользовательского ни на одной попытке —
   посетитель не получит подтверждения вообще, хотя его адрес исправен.
3. **Статус врёт.** `Failed` при уже доставленном админском письме;
   `error_message` перезатирается на каждой попытке.

**Трасса:** `handle()` не сверяется со статусом и не трекает, какое плечо
уже завершено; общий `try` на оба плеча.

**Почему не теория:** `MAIL_MAILER=smtp`, частая причина падения
пользовательского плеча — опечатка в email посетителя, SMTP отбивает
`RCPT TO`.

**Фикс:** отложен 2026-08-04 как некритичный. Разобранный план с выбором
варианта, решениями и тестами —
[plans/form-mail-idempotency/README.md](form-mail-idempotency/README.md).

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

### 16. Счётчики категорий stale до 10 мин после наступления `published_at`

**Файл:** `app/Livewire/Components/AbstractContentFull.php:83-90`

**Симптом:** `getCategories()` кэширует результат `buildCategoriesQuery`
(включая `posts_count`/`projects_count`) на 600 секунд по тегам
`['news','categories']` / `['projects','categories']`. Кэш
инвалидируется только на `Post|Project|Category::saved|deleted`.

После фикса #15 счётчик правильно **не** учитывает будущие публикации,
а `NewsQuery::list()` — вообще без кэша. Значит, когда `published_at`
наступает **по расписанию** (без save/update), карточки появляются
сразу, а счётчик обновится только на следующем `saved|deleted` или
через 10 минут TTL.

Окно расхождения: ≤ 10 минут после наступления `published_at`.

**Почему P3:** редкий сценарий (посты обычно публикуются вручную и уже
`published`), расхождение временное и в сторону занижения (счётчик <
реального). Не ломает функциональность.

**Варианты фикса:**

- Планировщик: `Schedule::call(fn () => cache()->tags(['news','categories','projects'])->flush())->everyMinute()` в
  `routes/console.php`. Дёшево, но флашит и не связанное.
- Планировщик умнее: раз в минуту `Post::query()->where('status', Published)->where('published_at', '<=', now())->whereRaw('published_at > NOW() - INTERVAL 2 MINUTE')`
  → если есть — флашить теги.
- Убрать кэш из `getCategories` целиком (тогда каждый рендер `/news` и
  `/projects` даёт лишний SELECT на categories + withCount).
- Считать `posts_count` inline в SQL без кэша (короткий запрос).

**Тест:** имитировать наступление `published_at` через `Carbon::setTestNow`
+ проверить, что кэш инвалидирован или сразу пересчитывается.

---

## Проверено — не баг

- **Опциональный `select` с плейсхолдером отправляется штатно.**
  `FormRulesBuilder::optionValues()` выбрасывает пустые значения из
  `Rule::in()`, а в state лежит `''` — выглядит так, будто `nullable` +
  `in:` обязаны ронять отправку. Не роняют: Laravel пропускает
  неимплицитные правила для пустой строки
  (`Validator::presentOrRuleIsImplicit`). Обе ветки — optional проходит,
  required падает — закреплены в `PublicFormTest::
test_select_placeholder_passes_when_optional_and_blocks_when_required`.

---

## Definition of done секции

- P0: #1 и #3 закрыты с регресс-тестами, #2 сознательно отложен с планом
  в [form-mail-idempotency](form-mail-idempotency/README.md).
- P1: закрыты все (#4, #5, #6, #15) — часть исправлением, часть решением
  «не чиним», обоснования в [archived/bugs.md](archived/bugs.md).
- P2/P3 — по приоритету, все открыты.
- Чек-лист `bugs-checklist.md` синхронен с этим файлом.
- Закрытый пункт переезжает в `archived/bugs.md` целиком, вместе с
  обоснованием и принятыми остаточными рисками.
- Баги, найденные в ревью после 2026-07-14, дописываются сюда со
  сквозной нумерацией (последний номер — 21).
