# Блок «Якорь» (Anchor)

## Задача

Служебный блок в контент-билдере: пустой `<div id="…">` на фронте, чтобы
можно было сослаться на участок страницы по хеш-ссылке
(`https://site.tld/page#my-section`). Ничего видимого не рендерит.

Промт-донор пришёл из другого проекта (Filament v5), адаптирован под наш
Filament v4 в чате 2026-07-29.

## Ключевые решения

- **Уникальность slug — через всю страницу**, включая вложенные `TabsBlock`
  и `ModalBlock`. `$get('../../../')` из промта видит только текущий Builder
  и **не подходит** — нужен обход всего дерева состояния формы.
  Реализация: в rule-замыкании инжектим `$livewire` (Filament DI это
  поддерживает, см. `vendor/filament/schemas/src/Components/Component.php:87`),
  берём `$livewire->data` (полное состояние формы Page/Post/Project),
  рекурсивно собираем все элементы с `type === 'anchor'` в дереве и считаем.
- **`->live(onBlur: true)`** оставляем — валидация всё равно основная на
  сабмите, но `live` подтолкнёт пересчёт визуального ошибочного состояния.
- **`->alphaDash()` + `maxLength(100)`** — достаточно для HTML `id`; экранирование в blade `{{ }}`.
- **HTML — в blade** (`resources/views/components/sections/anchor.blade.php`),
  не строкой в рендерере (проектная конвенция, см. `.ai/skills/add-block.md`).
- **Регистрация во всех секциях** — `all()`, `topSection()`, `mainSection()`,
  `bottomSection()`, `tabs()`, `modal()`.
- **Иконка `heroicon-o-link`** — да, несмотря на то что другие блоки без
  иконки. Задача решена в диалоге.
- **Отступ под sticky-header** — уровень CSS (`scroll-padding-top` /
  `scroll-mt-*`), не рендерера. В `resources/css/app.css` sticky-правил
  сейчас нет; если после добавления якорей выяснится, что шапка (в blade)
  sticky и перекрывает якоря — отдельным шагом добавить
  `html { scroll-padding-top: <высота шапки>px }`.

## Файлы

- **NEW** `app/Filament/Components/Anchor.php` — `key()`, `block()` с
  `TextInput('anchor')` + rule через `$livewire->data` + приватный
  `collectAnchorCounts(mixed $state): array<string, int>` (рекурсивный
  обход: если элемент массива имеет `['type' => 'anchor', 'data' => …]`,
  берём `data.anchor`; рекурсивно спускаемся в любые вложенные массивы).
- **NEW** `app/Blocks/Renderers/AnchorRenderer.php` — тонкий, отдаёт
  `view('components.sections.anchor', $data)->render()`.
- **NEW** `resources/views/components/sections/anchor.blade.php` — `@if
(! blank($anchor ?? null)) <div id="{{ $anchor }}"></div> @endif`.
- **EDIT** `app/Filament/Components/BlockRegistry/BlockRegistry.php` —
  импорт + `Anchor::block(),` в `all()` / `topSection()` / `mainSection()`
  / `bottomSection()` / `tabs()` / `modal()`.
- **EDIT** `app/Blocks/BlockRenderRegistry.php` — импорт +
  `AnchorRenderer::key() => AnchorRenderer::class,` в `map()`.
- **EDIT** `lang/ru/panel.php` — `'anchor' => 'Якорь'`,
  `'anchor_duplicate' => 'Якорь ":anchor" уже используется'`.
- **EDIT** `lang/en/panel.php` — `'anchor' => 'Anchor'`,
  `'anchor_duplicate' => 'Anchor ":anchor" is already used'`.

## Скелет rule (для реализующего)

```php
->rule(function (\Livewire\Component $livewire) {
    return function (string $attribute, $value, \Closure $fail) use ($livewire) {
        if (blank($value)) {
            return;
        }

        $counts = self::collectAnchorCounts($livewire->data ?? []);

        if (($counts[$value] ?? 0) > 1) {
            $fail(__('panel.anchor_duplicate', ['anchor' => $value]));
        }
    };
})
```

`collectAnchorCounts`:

- if `! is_array($state)` → `[]`
- iterate values: если `is_array($value)` и
  `($value['type'] ?? null) === 'anchor'` и `filled($value['data']['anchor'] ?? null)`
  → инкрементим счётчик; **всё равно** рекурсивно спускаемся в `$value`
  (Anchor не имеет вложенных блоков, лишнего не насчитает).

## Order of work

1. **Проверка контракта Filament DI перед кодом.** В любом существующем
   Filament-хуке коротко `dd($livewire->data)` из create/edit-страницы Page
   — убедиться, что структура — плоский массив с `top_section`,
   `main_section`, `bottom_section` (и что Builder-items внутри имеют
   форму `[uuid => ['type' => …, 'data' => [...]]]`). После проверки — убрать `dd`.
2. Blade + языковые ключи (`ru`/`en`) + `AnchorRenderer`.
3. `Anchor` Filament-компонент с `collectAnchorCounts` и rule через `$livewire`.
4. Регистрация в обоих реестрах (все 6 методов `BlockRegistry`).
5. Проверка (см. ниже) + `pint --dirty` + `npm run format` для blade.

## Проверка

1. В админке (Page/Post/Project) добавь блок «Якорь» в `main_section`,
   введи `test`, сохрани → сохраняется.
2. Добавь второй якорь с `test` в **`top_section`** той же страницы → на
   сабмите вылезает «Якорь "test" уже используется».
3. Добавь `TabsBlock` в `main_section`, внутри одной из вкладок — Anchor
   c `test` → тоже ловится как дубль.
4. На фронте страница со значением `test` → в HTML есть `<div id="test"></div>`;
   `#test` в URL скроллит.
5. Пустой якорь — не рендерит, не ошибается.

## Boundaries — что НЕ входит

- Кнопка «Copy link» рядом с полем в админке.
- Автогенерация slug из соседнего `Title`.
- UI-выбор существующих якорей страницы в `UrlInput`.
- TOC-блок, собирающий якори.
- `scroll-padding-top` для sticky-шапки (отдельным шагом, если понадобится).

## Checklist

- [ ] Проверить структуру `$livewire->data` на реальной Page-форме (`dd`, потом убрать) — пропущено, доверяем структуре Filament v4 Builder items; при поломке — сделать в первую очередь
- [x] `AnchorRenderer` + `anchor.blade.php`
- [x] Языковые ключи в `lang/ru/panel.php` и `lang/en/panel.php`
- [x] `Anchor` компонент: `collectAnchorCounts` + rule через `$livewire->data`
- [x] Регистрация в `BlockRenderRegistry::map()`
- [x] Регистрация в `BlockRegistry::all()` / `topSection()` / `mainSection()` / `bottomSection()` / `tabs()` / `modal()`
- [x] `BlockRegistryTest` — обновлены `all/top/bottom` expected-массивы под нового `anchor`
- [x] `BlockRenderRegistryTest` — добавлен ассерт по `AnchorRenderer`
- [x] `AnchorRendererTest` — 4 теста: key/version, рендер div, пусто, экранирование XSS
- [x] `AnchorTest` — 5 тестов: key/getName, `collectAnchorCounts` на плоском Builder, через 3 секции страницы, через вложенные Tabs+Modal, blank/non-string фильтр (через reflection)
- [ ] Ручная проверка в админке и на фронте (браузер): дубль в top+main, дубль через Tabs, `#test` скроллит
- [x] `.ai/file-map.md` — упомянут `Anchor` в списке блоков + описание отличия
- [x] `vendor/bin/sail bin pint --dirty` + `npm run format` (флаг `--format agent` не поддерживается текущей pint)
- [x] `vendor/bin/sail artisan test --compact` — **261 passed** (было 252, +9 новых)
