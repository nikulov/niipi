# Блок «Поделиться + кнопка» (archived)

Сделано 2026-08-06, ветка `staging`.

## Цель

Блок `share-button` — копия блока `button` с шарингом: слева от основной
кнопки триггер, по клику из-под него вправо выезжает полоска и **наезжает
поверх** основной кнопки (`absolute`), макет не дёргается. Внутри полоски —
ВК, Telegram, MAX и «копировать ссылку».

## Что сделано

- `app/Filament/Components/ShareButton.php` — схема **идентична `Button`**
  (`btnLabel`, `btnUrl`, `btnType`, `btnPosition`, `blank`), соцсетей в
  админке нет.
- `app/Blocks/Renderers/ShareButtonRenderer.php` — ключ `share-button`,
  версия `1`, рендер шаблона напрямую.
- `resources/views/components/sections/share-button.blade.php` — инлайновый
  `x-data` (`open`, `copied`, `share()`, `copy()`), `@click.outside` +
  `@keydown.escape.window`, полоска `absolute top-0 left-full` с
  `x-show`/`x-transition`.
- `resources/views/components/icon/icon-{share,vk,telegram,max,link}.blade.php`.
- Регистрация: `BlockRenderRegistry::map()` + `BlockRegistry::all()`,
  `mainSection()`, `tabs()`, `modal()`.
- `lang/{ru,en}/page.php` — `share`, `copy_link`, `link_copied`;
  `lang/{ru,en}/panel.php` — `share_btn`. Заодно добавлен потерянный
  `panel.svg` в `lang/en` (использовался `FooterForm` и
  `ImageTittleFullWidth`, в английской локали ключа не было).
- `resources/css/app.css` — во все четыре `.btn-*-bg` вложен
  `&[aria-expanded='true']` с ховер-цветами: пока полоска открыта, триггер
  держит цвет. `aria-expanded` во всём проекте только у этого триггера.
- Тест: `tests/Unit/Blocks/Renderers/ShareButtonRendererTest.php` + строка
  `share-button` в `BlockRegistryTest`.

## Принятые решения

- **В админке ничего не настраивается.** Сначала пробовали репитер со
  ссылками — он требовал вбивать полный адрес страницы в каждую ссылку
  (шарилкам нужен `?url=`), то есть заполнять блок заново на каждой
  странице. Набор кнопок и иконки зашиты в шаблон.
- **Адреса собираются в браузере**: Alpine-хелпер `share()` подставляет
  `location.href` и `document.title` в шаблоны с `{url}` / `{title}`.
  Блок шарит ту страницу, на которой стоит.
- **У MAX веб-диалога шаринга нет** — только диплинк в приложение
  `https://max.ru/:share?text=<текст>` ([dev.max.ru/help/deeplinks](https://dev.max.ru/help/deeplinks),
  iOS 2.7+ / Android 2.9+). `text` — тело сообщения, не отдельный `url`,
  поэтому шарим голую ссылку без заголовка. На десктопе без установленного
  MAX клик уводит на `max.ru` — ожидаемо.
- **Подсказка «Ссылка скопирована» живёт вне полоски**: `clip-path` обрезает
  потомков, внутри полоски она была невидима.
- **Открытое состояние — через `[aria-expanded='true']`, а не класс**:
  атрибут уже висел на триггере, `hover:` у Tailwind завёрнут в
  `@media (hover: hover)` и на тач-устройствах не срабатывает вовсе.
- **`app.js` не трогали**, новых классов в `app.css` под вёрстку не заводили —
  срезанные углы через arbitrary `[clip-path:polygon(8px_0,…)]`.

## Границы

Блок `button` остался без шаринга. Счётчиков репостов, OG-разметки и Web
Share API не делали.

## Хвост

`icon-link` остался заглушкой — настоящий SVG ставит пользователь.
