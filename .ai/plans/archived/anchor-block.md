# Блок «Якорь» (archived)

Реализовано и закоммичено в `staging` 2026-07-29 (`16b2c55`).

## Цель

Служебный блок в контент-билдере, рендерящий пустой `<div id="…">` — точка
для хеш-ссылок вида `https://site.tld/page#my-section`. Дубликаты slug-ов
запрещены в пределах всей страницы, включая вложенные Tabs/Modal.

## Что сделано

**Backend:**

- `app/Filament/Components/Anchor.php` — блок с `TextInput('anchor')`,
  префикс `#`, `alphaDash + maxLength(100)`, `trim`, `live(onBlur: true)`,
  иконка `heroicon-o-link`.
- Уникальность slug — через `->rule(function ($livewire) {...})`, использует
  Filament DI (`$livewire` — из `vendor/filament/schemas/src/Components/Component.php:87`)
  и берёт `$livewire->data` — полное состояние формы, включая все Builder-поля
  и всё, что лежит внутри `tabs-block`/`modal-block`. Приватный
  `walkAnchors()` рекурсивно обходит массив; `collectAnchorCounts()`
  возвращает `slug => count`.
- `app/Blocks/Renderers/AnchorRenderer.php` — тонкий, отдаёт
  `view('components.sections.anchor', $data)`.
- `resources/views/components/sections/anchor.blade.php` — `@if (! blank(...)) <div id="{{ $anchor }}"></div> @endif`, экранирование через `{{ }}`.

**Регистрация:**

- `app/Blocks/BlockRenderRegistry.php::map()` — `anchor => AnchorRenderer::class`.
- `app/Filament/Components/BlockRegistry/BlockRegistry.php` — во всех 6 методах
  (`all`, `topSection`, `mainSection`, `bottomSection`, `tabs`, `modal`).

**Локализация:**

- `lang/{ru,en}/panel.php` — `anchor`, `anchor_duplicate` (с плейсхолдером
  `:anchor`).

**Тесты (+9, было 252 → 261 passed):**

- `tests/Unit/Blocks/Renderers/AnchorRendererTest.php` — 4: key/version, рендер
  `<div id>`, пусто, XSS-экранирование.
- `tests/Unit/Filament/Components/AnchorTest.php` — 5: name/getName, плоский
  Builder, три секции страницы, вложенные Tabs+Modal, blank/non-string фильтр
  (доступ к private через reflection).
- `tests/Unit/Blocks/Renderers/BlockRenderRegistryTest.php` — добавлен ассерт по `AnchorRenderer`.
- `tests/Unit/Filament/Components/BlockRegistry/BlockRegistryTest.php` — `all/top/bottom` expected-массивы под нового `anchor`.

**Doc:** `.ai/file-map.md` — упоминание Anchor в списке блоков + описание
отличия («уникальность по всей странице, не только внутри Builder»).

## Ключевые решения

- **Уникальность через всю форму, не в текущем Builder.** `$get('../../../')`
  из промт-донора видит только текущий Builder — не годится для Tabs/Modal и
  для трёх page-level builders. Заменено на `$livewire->data` + рекурсивный
  walker.
- **Регистрация везде.** Anchor допустим в top/main/bottom + tabs/modal —
  админ может ставить его в любом месте страницы.
- **Иконка `heroicon-o-link`.** Единственный блок в проекте с `->icon()`
  сейчас — но семантически оправдано.
- **HTML в blade**, не строкой в рендерере (проектная конвенция).

## Что НЕ вошло (осознанно)

- Проверка `dd($livewire->data)` из чек-листа — пропущена, доверяем стандартной
  структуре Filament v4 Builder items (`[uuid => ['type', 'data']]`). Тесты
  собирают эту форму руками и подтверждают, что walker её правильно понимает.
- Ручная браузерная проверка (дубль между секциями, дубль через Tabs, скролл
  по `#test`) — не выполнена в сессии; smoke check через tinker подтвердил
  инстанс блока, рендерер и регистрацию во всех 6 секциях.
- Кнопка «Copy link» рядом с полем, автогенерация slug из соседнего Title,
  UI-выбор существующих якорей в `UrlInput`, TOC-блок, CSS
  `scroll-padding-top` для sticky-шапки (шапка сейчас не sticky).

## Коммит

| Дата       | SHA       | Описание                              |
| ---------- | --------- | ------------------------------------- |
| 2026-07-29 | `16b2c55` | add anchor block for hash-link targets |

## Ключевые файлы (для контекста будущих сессий)

- `app/Filament/Components/Anchor.php` — блок + `walkAnchors()` (частная
  логика уникальности через всё дерево формы).
- `app/Blocks/Renderers/AnchorRenderer.php` +
  `resources/views/components/sections/anchor.blade.php`.
- Регистрации: `app/Blocks/BlockRenderRegistry.php`,
  `app/Filament/Components/BlockRegistry/BlockRegistry.php`.
- Тесты: `tests/Unit/Blocks/Renderers/AnchorRendererTest.php`,
  `tests/Unit/Filament/Components/AnchorTest.php`.
