# Блок «Тематическая подборка» (archived)

Реализовано 2026-08-03 (`2feaf76`, `aa49322`), доработано по ревью 2026-08-04.

## Цель

Полиморфный блок `related-thematic` для Post и Project: сетка карточек
связанного контента по категориям текущей записи.

## Что сделано

- `app/Filament/Components/RelatedThematic.php` — схема блока (`title`,
  `limit`, `categoryIds`). Select категорий фильтруется по типу модели:
  тип определяется через `$livewire->getRecord()` (Edit) или
  `$livewire::getResource()::getModel()` (Create, где record ещё null).
- `app/Blocks/Renderers/RelatedThematicRenderer.php` — ветвление по
  `instanceof Post|Project`, `limit` кламппится 1..20, `categoryIds` из
  data имеет приоритет над категориями модели, текущая запись
  исключается через новый параметр `excludeId` в
  `NewsQuery::list()`/`ProjectsQuery::list()`.
- `resources/views/components/sections/related-thematic.blade.php` —
  заголовок + сетка 2/3/5 квадратных карточек.
- Регистрация: `BlockRenderRegistry::map()`,
  `BlockRegistry::all()` + `mainSection()` (в остальные секции не
  добавляли). Дефолтная вставка при создании записи —
  `CreatePost`/`CreateProject::appendDefaultMainBlock()` перед
  `CategoryList`.
- `lang/{ru,en}/panel.php` — `related_thematic_label`,
  `related_thematic`, `related_thematic_categories_hint`.
- Тесты: `tests/Unit/Blocks/Renderers/RelatedThematicRendererTest.php`,
  `tests/Unit/Services/{News,Projects}QueryTest.php` (лимит + `excludeId`).

## Что изменилось после ревью (2026-08-04)

Кнопка «Смотреть все» убрана целиком — вместе с полем `btnLabel`,
ключом `related_thematic_all_btn` и тестом на URL кнопки. Причина: она
строила ссылку по **первой категории записи**, игнорируя override
`data.categoryIds`, то есть подборка и ссылка могли указывать на разные
категории. В JSON записей, созданных 2026-08-03, мог остаться ключ
`btnLabel` — рендерер его игнорирует, миграция не нужна.

## Коммиты

| Дата       | SHA       | Описание                                      |
| ---------- | --------- | --------------------------------------------- |
| 2026-08-03 | `2feaf76` | add related-thematic content block            |
| 2026-08-03 | `aa49322` | add related-thematic to BlockRegistry all()    |
| 2026-08-04 | —         | удаление кнопки «Смотреть все» (см. выше)     |
