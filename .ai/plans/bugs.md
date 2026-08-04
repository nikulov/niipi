# План: баги и cleanup-задачи

Составлено после deep-review 2026-07-14 (после того, как P0/P1/P2/P3/P4
тестового покрытия закрыты). Компактный чек-лист статусов — в
[bugs-checklist.md](bugs-checklist.md).

Здесь только **открытое**: остался один P3-cleanup. Закрытые пункты
(#1–#12, #15, #17–#21) — в [archived/bugs.md](archived/bugs.md);
нумерация сквозная, номера не переиспользуются.

## Легенда

- **P0** — активный баг, ломает пользовательский поток, чинить срочно.
- **P1** — активный баг, но узкий сценарий/редкий трафик.
- **P2** — латентный (сработает при определённых условиях), либо cleanup
  с осязаемой ценностью.
- **P3** — code smell / микро-cleanup.

---

## P3 — cleanup

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

- P0 закрыт: #1 и #3 исправлены с регресс-тестами, #2 закрыт как
  won't fix — единственный из закрытых «не чиним», который **достижим
  на проде**; риск принят, готовый план сохранён в
  [archived/form-mail-idempotency.md](archived/form-mail-idempotency.md).
- P1 закрыт: #4, #5, #6, #15 — часть исправлением, часть решением
  «не чиним», обоснования в [archived/bugs.md](archived/bugs.md).
- P2 закрыт: #7 исправлен, #8 и #9 — won't fix, недостижимы через
  приложение, а у фикса #8 риск выше пользы.
- P3: #10, #11 и #12 исправлены, остались #13, #14, #16.
- Чек-лист `bugs-checklist.md` синхронен с этим файлом.
- Закрытый пункт переезжает в `archived/bugs.md` целиком, вместе с
  обоснованием и принятыми остаточными рисками.
- Баги, найденные в ревью после 2026-07-14, дописываются сюда со
  сквозной нумерацией (последний номер — 21).
