# Архив: закрытые баги

Закрытые пункты из [bugs.md](../bugs.md). Нумерация сквозная и общая с
живым файлом — номера не переиспользуются. Открытое осталось там.

| № | Суть | Как закрыт |
| --- | --- | --- |
| 1 | `parseExtraRules` роняет list-form правила | исправлен, `12bd4dd` |
| 3 | Осиротевшие файлы при откате транзакции | исправлен, `85f7630` + `a484fdb` |
| 4 | `getSectionOption` не видит второй блок-настройку | закрыт со стороны админки, `maxItems(1)` |
| 5 | «Новых сегодня» не фильтрует по статусу | закрыт как косметика, не чинили |
| 6 | Update статуса вне транзакции | исправлен, `85f7630` |
| 7 | `Post $post` в `forceDelete` у 11 полиси | исправлен, `de3fd5f` |
| 10 | Пустой try/catch в `PublicForm::submit` | исправлен, `64c132c` |
| 12 | `ProjectObserver` принимает `$post` | исправлен, `d58ce9b` |
| 15 | Счётчики категорий включают будущие публикации | исправлен, `b88045e` |
| 17 | «Смотреть все» игнорировала override категорий | исправлен, `4e2e324` |
| 18 | Плейсхолдер-опция протекала в `radio` | исправлен, `16c934f` |
| 19 | Дефолт `radio` не отражался в разметке | исправлен, `16c934f` |
| 20 | Несколько `default: true` — DOM расходился со state | исправлен, `16c934f` |
| 21 | Опция с `value: "0"` не могла быть дефолтом | исправлен, `16c934f` |

---

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

### 4. ~~`HasSectionOptions::getSectionOption` возвращает null от первого пустого блока~~ ✅ закрыт

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

**Решение 2026-08-04: закрыт вторым вариантом, логику чтения не меняли.**

- `BgForMainSection::block()` получил `->maxItems(1)` — Filament убирает
  тип из пикера, как только экземпляр уже есть в билдере (счёт отдельный
  на каждую секцию, см. `Builder::getBlockPickerBlocks`). Второй блок
  через админку теперь не добавить.
- Блок убран из `BlockRegistry::tabs()` и `::modal()` — остался только в
  `all()` и `mainSection()`. Во вложенных билдерах он всё равно не читался:
  `getBlocksForSection('main')` видит лишь верхний уровень JSON-колонки.
- `getSectionOption()` продолжает брать первый матч. Поведение закреплено
  тестом `test_get_section_option_reads_from_first_matching_block`.

**Принятый остаточный риск:** `maxItems` — фильтр пикера, а не валидация.
Записи с двумя блоками, сохранённые раньше, и данные из импорта код не
переживает: если у первого блока пусто, фон не покажется. Решено принять —
через UI такую пару больше не создать. Если всплывёт на реальных данных,
фикс известен: пропускать `blank($value)` и идти к следующему блоку.

**Смежное (не баг):** через админку пустой блок не сохранить —
`FileUpload::make('bgForMainSection')` помечен `->required()`. Если
обязательность снимут, ошибки не будет: `content-block.blade.php:8`
оборачивает `style` в `@if (! empty(...))`, а `ContentRenderer:53`
пропускает option-блоки при рендере. Деградация молчаливая — просто нет
фона.

---

### 5. ~~`SubmissionsStats` «Новых сегодня» не фильтрует по статусу~~ ❌ закрыт как косметика

**Файл:** `app/Filament/Widgets/SubmissionsStats.php:22`

**Симптом:** счётчик показывает **все** отправки за день (включая
`Sent`, `Failed`, `Processing`), хотя лейбл «Новые сегодня» и соседние
счётчики фильтруют по статусу. Соседние при этом не фильтруют по дате —
на плитках перемешаны две оси.

**Решение 2026-08-04: не чиним, оставляем как есть.** Считается корректное
число — дневной приток заявок; расходится с ним только подпись. Ничего не
ломается, никаких неверных вычислений, максимум админ прочитает «Новые»
как «необработанные».

**Записанный ранее фикс (`->where('status', New)`) применять нельзя** — он
устарел после #6: `New` больше не переживает транзакцию, плитка показывала
бы 0 всегда. Если вернёмся — правильный вариант A: переименовать ключ
`new_today` → `submissions_today` («Заявок сегодня» / «Submissions today»)
и заодно заменить сырые `'processing'`/`'failed'` на значения энума.

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

### 7. ~~Type-hint `Post $post` в forceDelete (11 полиси)~~ ✅ исправлено

**Файлы:** `CategoryPolicy`, `FooterPolicy`, `FormFieldPolicy`, `FormPolicy`,
`FormSubmissionFilePolicy`, `FormSubmissionPolicy`, `GlobalSettingPolicy`,
`MenuPolicy`, `PagePolicy`, `ProjectPolicy`, `UserPolicy` — во всех
`forceDelete(User $user, Post $post)`, копипаста из `PostPolicy`.

**Симптом:** сигнатура ждёт `Post`, а Laravel передаст модель самой
полиси → `TypeError`.

**Нашлось при фиксе (в исходной записи не было):** в 10 из 11 файлов
`App\Models\Post` вообще **не импортирован**. В неймспейсе `App\Policies`
голое `Post` резолвится в `App\Policies\Post` — класса с таким именем нет,
то есть под такой аргумент не подходит вообще ничто. Импорт был только в
`ProjectPolicy` (и в `PostPolicy`, где хинт правильный).

**Почему не стреляло:** `forceDelete` вызывается только при мягком
удалении, `SoftDeletes` нет ни в одной модели → Filament кнопку не
показывает, право никто не запрашивает.

**Фикс:** в каждой полиси свой тип модели; имя параметра взято такое же,
как в соседних `view`/`update`/`delete` того же файла. В `ProjectPolicy`
дополнительно убран осиротевший `use App\Models\Post;` и `Project $post`
переименован в `$project` в `view`/`update`/`delete` (там же поправлен
закомментированный пример «только свои»). `PostPolicy` не тронут —
там хинт верный.

**Имена параметров** из той же копипасты добиты следом, отдельным
проходом: `Footer $form` → `$footer`, `Menu $page` → `$menu`,
`GlobalSetting $page` → `$globalSetting`, `User $post` → `$model`
(в `UserPolicy` оба аргумента типа `User`, имя `$user` занято первым).
В `FormPolicy`, `PagePolicy` и `PostPolicy` имена `$form`/`$page`/`$post`
законны — не трогали. Тела методов параметр нигде не используют, так что
правка чисто сигнатурная.

---

### 10. ~~Пустой try/catch в `PublicForm::submit`~~ ✅ исправлено

**Файл:** `app/Livewire/Forms/PublicForm.php:99`

`catch (ValidationException $e) { throw $e; }` — no-op: ловит и тут же
пробрасывает.

**Фикс:** обёртка снята, тело поднято на уровень выше. Импорт
`Illuminate\Validation\ValidationException` стал неиспользуемым и удалён.
Поведение не изменилось — исключение как летело наружу, так и летит.

---

### 12. ~~`ProjectObserver` параметр называется `$post`~~ ✅ исправлено

**Файл:** `app/Observers/ProjectObserver.php:10`

`saving(Project $post)` — копипаста из `PostObserver`, тип верный, имя
врёт.

**Фикс:** `$post` → `$project`, вместе с тремя обращениями в теле.

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

**Фикс:** коммит `b88045e`.
- В обоих `buildCategoriesQuery` — `->where('published_at', '<=', now())`
  внутрь замыкания `withCount`.
- В `AbstractContentFull::getTotalCount` — `->where($contentTable.'.published_at', '<=', now())`
  после фильтра по статусу.
- Тесты: `tests/Feature/Livewire/NewsFullTest.php`, `ProjectsFullTest.php` —
  кейс «пост с будущим `published_at` не учитывается в счётчике».

**Хвост:** счётчики остаются stale до 10 минут, когда `published_at`
наступает по расписанию, — это отдельный открытый пункт #16 в
[bugs.md](../bugs.md).

**Почему был P1, не P0:** визуальное расхождение цифр в списке категорий.
Не ломает функциональность, но выглядит как баг.

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
