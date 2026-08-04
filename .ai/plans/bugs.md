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

### 1. ~~`FormRulesBuilder::parseExtraRules` тихо роняет list-form правила~~ ✅ исправлено

**Файл:** `app/Services/Forms/FormRulesBuilder.php:170`

**Симптом:** пользовательские правила валидации из
`FormField::$rules = ['min:3', 'email']` (list-форма JSON) не применяются
— поле не проверяется вовсе. Работает только assoc-форма
`['min:3' => 'message']`.

**Трасса:** `array_keys(['min:3']) = [0]`, `range(0, 0) = [0]`, `[0] !== [0]` →
`false` → `$isAssoc = false` → `return [[], $messages]`.

**Контекст:** админ-UI (`FieldsRelationManager.php:131`) — свободный JSON,
не запрещает list-форму → админ легко напишет её и ничего не заметит.

**Фикс:** коммит `12bd4dd`. Один проход по массиву разбирает все три формы:
int-ключ несёт правило, string-ключ — пару `правило => сообщение`, пустые
и не-string правила отбрасываются. Побочно закрыт худший кейс — смешанная
форма, где `array_keys()` протаскивал int-ключ `0` в набор правил и
валидатор падал с `BadMethodCallException`. Сообщения по-прежнему
привязываются только к assoc-записям, list-форма в helper-text
(`rules_help`) описана.

**Тест:** `tests/Unit/Services/Forms/FormRulesBuilderTest.php` — добавлены
кейсы list-формы и смешанной.

---

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

### 3. ~~`SubmitFormAction` оставляет осиротевшие файлы при откате транзакции~~ ✅ исправлено

**Файлы:** `app/Actions/Forms/SubmitFormAction.php:57`,
`app/Services/Forms/SubmissionFilesStorer.php:45`

**Симптом:** `$upload->store()` пишет файл на диск **до** `FormSubmissionFile::create`;
если DB-транзакция откатится (deadlock, constraint), файл остаётся в
`storage/app/public/forms/{formId}/{submissionId}/` без строки в БД.

**Фикс:** коммиты `85f7630`, `a484fdb`. Выбран первый вариант:
`SubmissionFilesStorer::store()` получил четвёртый параметр
`array &$stored`, куда пишет `disk`/`path` **до** `FormSubmissionFile::create`;
`SubmitFormAction` обернул `DB::transaction` в `try/catch` и удаляет
накопленные пути перед пробросом исключения. Передача по ссылке нужна
именно для падения на середине `store()` — о файле, чья строка ещё не
создана, узнать больше неоткуда. Каждый `delete` завёрнут в `rescue()`,
чтобы сбой уборки не подменил собой исходную причину отката.

**Тесты:** `tests/Unit/Actions/SubmitFormActionTest.php` — откат после
`store()` и откат на середине `store()` (multi-поле, падение на втором
файле). Оба фиксируют предусловие «файлы были на диске» снимком
`allFiles()` изнутри listener'а: без него тест остаётся зелёным в мире,
где `store()` вообще не вызывался.

**Осталось:** `Storage::delete()` оставляет пустой каталог
`forms/{formId}/{submissionId}/`; `UploadedFile::store()` может вернуть
`false` и тогда в `$stored` ляжет `['path' => false]` вразрез с докблоком
(то же значение давно пишется в `FormSubmissionFile.path`).

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

### 6. ~~`SubmitFormAction::handle` — update статуса вне транзакции~~ ✅ исправлено

**Файл:** `app/Actions/Forms/SubmitFormAction.php:67`

**Симптом:** `$submission->update(['status' => Processing])` идёт **после**
`DB::transaction`. Если update упадёт (сетевой сбой БД в момент между
commit и update), submission останется в статусе `New` с уже
загруженными файлами и НЕбез диспатча job'а.

**Фикс:** коммит `85f7630`. Взят первый вариант — update переехал внутрь
транзакции, последним шагом перед `return`. Диспатч
`SendFormSubmissionEmails` остался снаружи, после коммита.

**Хвосты выбранного варианта** (обсуждаемо, второй вариант их снимает):

- На каждую отправку остаётся лишний UPDATE: `SubmissionCreator` пишет
  `New`, следом транзакция переводит в `Processing`.
- `FormSubmissionStatus::New` больше не наблюдаем снаружи транзакции — в
  `app/` он читается только в `SubmissionCreator`, так что в фильтре
  статусов админки это теперь всегда пустой пункт.
- Тест отката (#3) завязан на этот самый UPDATE — ловит
  `FormSubmission::updating`. Переход на второй вариант (`Processing`
  сразу в `SubmissionCreator::create`) потребует переписать его на другой
  триггер.

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

## Найдено и исправлено в ревью 2026-08-04

Ревью правок сессии 2026-08-03 (`4a89f3b..a437c80`). Все пять уже в
`staging` — коммиты `4e2e324` и `16c934f`.

### 17. ~~Кнопка «Смотреть все» в `related-thematic` игнорировала override категорий~~ ✅ исправлено

**Файл:** `app/Blocks/Renderers/RelatedThematicRenderer.php:67` (удалён)

URL строился по первой категории записи, а подборка — по
`data.categoryIds`, если он задан. Итог: сетка из категории X, ссылка на
Y; при пустых категориях записи — `/news` вообще без фильтра.

**Фикс:** кнопка удалена целиком вместе с полем `btnLabel` и ключом
`related_thematic_all_btn`.

---

### 18. ~~Плейсхолдер-опция протекала в `radio`~~ ✅ исправлено

**Файл:** `app/Presenters/Forms/PublicFormPresenter.php:81`

`normalizeOptions` пропускал пустой `value` при `disabled: true` для
любого типа поля, а `radio.blade.php` про `disabled` не знал → строка
плейсхолдера рендерилась обычной выбираемой радиокнопкой с пустым
значением.

**Фикс:** `normalizeOptions($options, $type)` — исключение только для
`select`; в `radio.blade.php` добавлен `@disabled`.

---

### 19. ~~Дефолт `radio` не отражался в разметке~~ ✅ исправлено

**Файл:** `resources/views/components/form/fields/radio.blade.php`

`applySelectAndRadioDefaults` клал дефолт в state, `select.blade.php`
получил `@selected` (`d9a5919`), а radio — нет. Визуально ничего не было
отмечено, но форма отправляла дефолт.

**Фикс:** `@checked($opt['default'] ?? false)`.

---

### 20. ~~Несколько `default: true` — DOM расходился со state~~ ✅ исправлено

**Файл:** `app/Presenters/Forms/PublicFormPresenter.php`

`extractDefault` брала первую помеченную опцию, а `@selected` помечал
все → браузер применял последнюю.

**Фикс:** `normalizeOptions` гасит флаг у всех, кроме первой;
`extractDefault` читает уже нормализованный список, а не сырой JSON.

---

### 21. ~~Опция с `value: "0"` не могла быть дефолтом~~ ✅ исправлено

**Файл:** `app/Presenters/Forms/PublicFormPresenter.php:119` (в старой редакции)

`! empty($row['value'])` считает `"0"` пустым → опция отбрасывалась как
дефолт, хотя `normalizeOptions` её оставлял.

**Фикс:** ушёл вместе с рефактором из #20 — условие по сырому JSON
исчезло.

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

- P0 (пункты 1–3) закрыты, добавлены регресс-тесты.
- P1 (пункты 4–6) закрыты или сознательно отложены с записью в
  `.ai/decisions.md`.
- P2/P3 — по приоритету.
- Чек-лист `bugs-checklist.md` синхронен с этим файлом.
- Баги, найденные в ревью после 2026-07-14, дописываются сюда со
  сквозной нумерацией (последний номер — 21).
