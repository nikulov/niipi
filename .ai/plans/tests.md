# План: покрытие тестами

Инвентаризация на 2026-07-13. Прогон: `76 passed, 3 failed`. Замер по
файлам, а не по строкам (line coverage не собирался).

## Итог по слоям

| Слой | Классов | Тестов | Покрытие |
|---|---:|---:|---|
| Actions/Forms | 1 | 1 | ✅ полное |
| Blocks/BlockRenderRegistry | 1 | 1 | ✅ |
| Blocks/Renderers | 26 | 6 | ⚠️ 23% — 20 без тестов |
| Enums | 8 | 0 | ❌ 0% |
| Filament/Components (Block-компоненты) | 22 | 0 | ❌ (низкий приоритет — декларативная конфигурация) |
| Filament/Forms/Components | 2 | 0 | ❌ |
| Filament/Resources | 13 групп | 0 | ❌ (нужен smoke: страницы открываются) |
| Filament/Support/RoleAccessResource | 1 | 0 | ❌ (ключевая логика доступа) |
| Filament/Widgets/SubmissionsStats | 1 | 0 | ❌ |
| Http/Controllers/ContentController | 1 | 1 (Feature) | ✅ |
| Jobs/SendFormSubmissionEmails | 1 | 1 | ✅ |
| Livewire/Components + Forms | 3 + 1 | 3 (Feature) | ✅ |
| Livewire/Components/AbstractContentFull | 1 | косвенно | ⚠️ прямых нет, покрыт через наследников |
| Mail | 3 | 3 | ✅ |
| Models | 12 | 12 | ✅ (по одному на модель) |
| Models/Concerns/HasSectionOptions | 1 | 0 | ❌ |
| Observers | 3 | 3 | ✅ |
| Policies | 13 | 0 | ❌ 0% — критично |
| Presenters/Blocks | 4 | 4 | ✅ |
| Presenters/Forms | 2 | 2 | ✅ |
| Providers/AppServiceProvider | 1 | 1 | ✅ |
| Providers/AuthServiceProvoider | 1 | 0 | ➖ мёртвый код (см. `.ai/decisions.md`) |
| Providers/Filament/AdminPanelProvider | 1 | 1 | ✅ |
| Services/ContentRenderer | 1 | 1 | ✅ |
| Services/Forms/* | 6 | 6 | ⚠️ 2 падают (см. ниже) |
| Services/NewsQuery, ProjectsQuery | 2 | 2 | ✅ |
| View/Components/Menu (DesktopFooter, Top) | 2 | 0 | ❌ |
| View/Composers/FooterComposer | 1 | 0 | ❌ |
| helpers.php (`public_asset`) | 1 fn | 0 | ❌ |

## Падающие тесты — починить в первую очередь

Реализация — источник истины. Тест приводится к текущему поведению
реализации (см. memory `feedback_fix_tests_not_impl`).

- [x] **`FormEmailTemplateRenderer` — {{ files }}**.
  Тест раньше делал `setAttribute('url', ...)`, но accessor `url` в
  `FormSubmissionFile` считает URL из `disk`+`path` и preset игнорирует.
  Fix: тест теперь ставит `disk = public`, использует `Storage::fake` и
  сверяется с `Storage::disk('public')->url(...)`.
- [x] **`FormRulesBuilder`**.
  `build()` возвращает кортеж `[rules, messages]`, а не только массив
  правил; extra-rules воспринимаются только в assoc-форме
  `['min:3' => 'сообщение']`; `mimes:*` и `mimetypes:*` фильтруются
  из extras файловых полей (auto-mimetypes из `accept_mimes` покрывает).
  Fix: тест деструктурирует результат, использует assoc-форму правил,
  убрал `mimes:pdf`-ожидание, дополнительно проверяет `messages`.

DoD секции: `vendor/bin/sail artisan test --compact` — 0 failed. ✅
(79 passed на 2026-07-13).

## Что покрыть (по приоритету)

### P0 — безопасность и доступ

- [x] **`Filament/Support/RoleAccessResource`** — `shouldRegisterNavigation()`
  и `canViewAny()` для комбинаций `Admin / Editor / Viewer /
  неавторизован` × `allowedRoles = [Admin] / [Admin,Editor] / [Admin,Editor,Viewer]`.
  `tests/Unit/Filament/Support/RoleAccessResourceTest.php` — 4 теста.
- **Policies** (13 файлов, `tests/Unit/Policies/*Test.php`). На каждую:
  - Admin bypass через `BasePolicy::before()` (через `Gate::forUser`).
  - `viewAny/view/create/update/delete/deleteAny` для Editor и Viewer.
  - `forceDelete/forceDeleteAny` не тестируем — в реализации type-hint
    `Post $post` (копипаста), TypeError на других моделях. См. отдельный
    пункт в бэклоге ниже.
  - Хелпер `TestCase::userOfRole(UserRole $r)` создаёт пользователя.
  - [x] CategoryPolicy — Group A (viewAny/view = E+V; create/update = E; delete = false)
  - [x] PostPolicy — Group A
  - [x] ProjectPolicy — Group A
  - [x] FormPolicy — Group B (viewAny/view = V; всё остальное false)
  - [x] FormFieldPolicy — Group B
  - [x] FormSubmissionPolicy — Group B
  - [x] FormSubmissionFilePolicy — Group B
  - [x] PagePolicy — Group B
  - [x] UserPolicy — Group B
  - [x] FooterPolicy — Group C (всё false, Admin через before)
  - [x] GlobalSettingPolicy — Group C
  - [x] MenuPolicy — Group C
- [ ] Отдельный ридми/issue: `forceDelete(User, Post $post)` во всех
  полиси кроме PostPolicy — некорректный type-hint (копипаста). Кандидат
  на правку в отдельной задаче.
- [x] **`BasePolicy`** — тест хелперов `isEditor`, `isViewer`,
  `isEditorOrViewer` + `before()` c Admin. Test-double `BasePolicyDouble`
  открывает protected-хелперы наружу.

### P1 — публичный контент и блоки

- [x] **17 рендереров блоков** (`tests/Unit/Blocks/Renderers/`):
  Accordion, AccordionLight, Button, CardsBlockWithButton,
  CardsBlockWithImageTitle, CategoryList, Gallery, ImageFull, ImageText,
  ImageTittleFullWidth, InfoBlockWithAchievements, InfoBlockWithButtons,
  SliderFullWidth, TabsBlock, TextFull, Title, YandexMap.
  На каждый: `key()`, `version()`, `render()` возвращает строку.
  Для renderer'ов с логикой (Accordion, TabsBlock, CategoryList) —
  доп. проверки трансформации/branching.
  Общий стаб — `Tests\Support\StubHasBlockSections`.
- [x] **`Livewire/Components/AbstractContentFull`** — прямой тест
  `getPageName()` (с/без componentKey), `mount()` — нормализация
  categoryIds и дефолты. Минимальный конкретный подкласс
  `AbstractContentFullDouble` без DB. Остальные ветки (пагинация,
  фильтр по категории) остаются в Feature-тестах наследников.
- [x] **`Services/ContentRenderer`** — расширил тесты:
  - Уже покрыто: unknown-тип → лог warning, option-block пропускается,
    text-full рендерится, отсутствующий type пропускается.
  - Добавлен тест: исключение внутри рендерера → лог error, конвейер
    продолжает рендерить остальные блоки (bind `TitleRenderer` на
    падающий stub).

### P2 — модели-хелперы и глобальный слой

- [x] **`Models/Concerns/HasSectionOptions`** — `isSectionOptionBlock`,
  `getSectionOption` (первый матч), `getBgForMainSection`. Test-holder
  реализует `HasBlockSections`.
- [x] **`helpers.php` — `public_asset()`** — null/'', http/https/абсолютный
  путь возвращаются как есть, относительный → Storage disk 'public'.
- [x] **`View/Composers/FooterComposer`** — шарит `$footer` из
  `Footer::cachedData()`; null при отсутствии записи. Инвалидация кэша
  через `booted()` не тестируется отдельно — покрыта тестом модели.
- [x] **`View/Components/Menu/DesktopFooter`, `Menu/Top`** — `menuItems`
  инициализируется (массив), `render()` возвращает нужный view.

### P3 — Filament (админка)

- [x] **`Filament/Widgets/SubmissionsStats`** — счётчики
  `new_today`/`processing`/`failed` из `FormSubmission`. Test-double
  открывает `getStats()`; парсим Section через `getDefaultChildComponents()`
  (не нужен container).
- [x] **`Filament/Forms/Components/UrlInput`** — `normalize()` (5 веток:
  blank / http(s) / domain-like / leading slash / bare path); `setUp()`
  задаёт `prefix` и `maxLength`. Suffix Action не тестируется отдельно —
  требует Filament schema-контекст.
- [ ] **`Filament/Forms/Components/CustomRepeater`** — нумерация меток.
  Требует полного Filament schema/Livewire контекста для
  `parent::getItemLabel($key)`. Тест-double тут бесполезен (дублирует
  production-логику). Отложено до появления Livewire-тестов с формой.
- [x] **Filament/Components/BlockRegistry** — `all/topSection/mainSection/
  bottomSection/tabs/modal` содержат ожидаемые ключи блоков (сравнение
  по `Block::getName()`). Проверены исключения (tabs без tabs-block).
- [x] **Ресурс-smoke (10 ресурсов, не 13)** — `tests/Feature/Filament/ResourceAccessTest.php`:
  `#[DataProvider]` по индекс-URL для categories, footers, form-submissions,
  forms, global-settings, menus, pages, posts, projects, users; guest → 302
  `/admin/login`, admin → 200.

### P4 — Enums (низкая ценность, но быстро)

- [x] По одному тесту на enum (8 файлов) в `tests/Unit/Enums/`. На каждый:
  итерация по `self::cases()` вызывает `getLabel()`/`getColor()`/`getIcon()` —
  match() бросил бы `UnhandledMatchError` при пропущенном case. Где есть
  `options()` — проверяем состав ключей.

## Definition of done

- `vendor/bin/sail artisan test --compact` — 0 failed.
- Все P0 и P1 позиции реализованы или явно закрыты как «не нужно» с
  указанием причины в этом файле.
- Новые рецепты/паттерны, если появились — вынесены в `.ai/patterns/` или
  `.ai/skills/`.

## Не в скоупе

- Прогон `--coverage` (line coverage) — если понадобится, добавим отдельной
  задачей: нужен Xdebug/pcov в Sail-образе.
- Тесты на `Providers/AuthServiceProvoider` — по `.ai/decisions.md`
  это мёртвый код, кандидат на удаление.
- Тесты на `Http/Controllers/MenuController` — пустой класс без роутов.