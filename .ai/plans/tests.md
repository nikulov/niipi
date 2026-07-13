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

Не менять тест — чинить реализацию. Реализация должна соответствовать
описанному в тесте контракту.

- [ ] **`FormEmailTemplateRenderer::renderBodyText`** — `{{ files }}` должен
  подставлять пары `имя + URL` (тест ждёт полный URL в тексте).
  Файл: `app/Services/Forms/FormEmailTemplateRenderer.php`.
  Симптом: `Expected: "Files:\n- file.pdf" to contain "http://example.com/file.pdf"`.
- [ ] **`FormRulesBuilder::build`** — ожидаемые ключи `data.name` и
  `uploads.resume` не генерятся (`Undefined array key`).
  Файл: `app/Services/Forms/FormRulesBuilder.php`.
  Уточнить: под каким префиксом билдер сейчас складывает правила
  (`fields.*` вместо `data.*`?) и привести к контракту `data.<name>` /
  `uploads.<name>`.

DoD секции: `vendor/bin/sail artisan test --compact` — 0 failed.

## Что покрыть (по приоритету)

### P0 — безопасность и доступ

- [ ] **`Filament/Support/RoleAccessResource`** — `shouldRegisterNavigation()`
  и `canViewAny()` для комбинаций `Admin / Editor / Viewer /
  неавторизован` × `allowedRoles = [Admin] / [Admin,Editor] / [Admin,Editor,Viewer]`.
- [ ] **Policies** (13 файлов, `tests/Unit/Policies/*Test.php`). На каждую:
  - Admin bypass через `BasePolicy::before()`.
  - `viewAny/view/create/update/delete/restore/forceDelete` для Editor и Viewer.
  - Гость → false.
  Общий helper: `withUserOfRole(UserRole $r)` в `TestCase`.
- [ ] **`BasePolicy`** — тест хелперов `isEditor`, `isViewer`,
  `isEditorOrViewer` + `before()` c Admin.

### P1 — публичный контент и блоки

- [ ] **20 отсутствующих рендереров блоков** (`tests/Unit/Blocks/Renderers/`):
  Accordion, AccordionLight, Button, CardsBlockWithButton,
  CardsBlockWithImageTitle, CategoryList, Gallery, ImageFull, ImageText,
  ImageTittleFullWidth, InfoBlockWithAchievements, InfoBlockWithButtons,
  SliderFullWidth, TabsBlock, TextFull, Title, YandexMap.
  Что тестируем в каждом:
  - `type()` — идентификатор из реестра.
  - `view()` — путь blade-шаблона существует.
  - `data(array $block)` — на голом массиве возвращает ожидаемый DTO/массив
    (учитывая дефолты, missing keys, URL для картинок через `public_asset`).
  - Для `HasBlockSections`-рендереров (если применяется) — распределение
    по секциям.
  Шаблон: см. существующий `NewsBlockRendererTest`.
- [ ] **`Livewire/Components/AbstractContentFull`** — прямой тест
  (сейчас покрыт только через наследников). Пагинация, фильтр по
  категории, пустое состояние.
- [ ] **`Services/ContentRenderer`** — расширить: неизвестный тип блока
  (не в реестре) → пропуск/лог, а не исключение (проверить текущее
  поведение и зафиксировать).

### P2 — модели-хелперы и глобальный слой

- [ ] **`Models/Concerns/HasSectionOptions`** — извлечение блока
  `bg-for-main-section` и вычисление `sectionOption(...)`.
- [ ] **`helpers.php` — `public_asset()`** — 3 кейса: относительный путь
  → Storage URL, абсолютный `http(s)://`, пустая строка.
- [ ] **`View/Composers/FooterComposer`** — шарит `$footer` из
  `Footer::cachedData()`, кэш инвалидируется наблюдателем (если есть).
- [ ] **`View/Components/Menu/DesktopFooter`, `Menu/Top`** — рендерятся
  без ошибок с минимальным набором данных.

### P3 — Filament (админка)

- [ ] **`Filament/Widgets/SubmissionsStats`** — счётчики соответствуют
  `FormSubmissionStatus`.
- [ ] **`Filament/Forms/Components/UrlInput`** — префикс, кнопка открытия
  ссылки, значение при dehydrate.
- [ ] **`Filament/Forms/Components/CustomRepeater`** — нумерация меток.
- [ ] **Ресурс-smoke (13 ресурсов)** — на каждую пару Create/Edit/List:
  страница открывается для авторизованного `Admin`, недоступна для гостя.
  Через Livewire::test + Filament::testing helpers.
- [ ] **Filament/Components/BlockRegistry** — набор блоков в каждой
  секции (`topSection`, `mainSection`, `bottomSection`, `tabs`, `modal`)
  соответствует реестру рендереров.

### P4 — Enums (низкая ценность, но быстро)

- [ ] По одному тесту на enum (8 файлов), проверка `label()`, `color()`,
  `icon()` там где определены; полнота кейсов
  (`self::cases()` → все обработаны).

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