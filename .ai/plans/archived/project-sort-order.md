# Ручной порядок проектов (`sort_order`)

**Цель.** Проекты выводились строго по `published_at` desc. Нужен ручной
приоритет: `1` — первый, затем `2`, `3`…; `0` или пусто — в конец списка;
внутри одного значения — по дате публикации, свежие сверху.

## Что сделано (2026-08-05)

- Миграция `2026_08_05_162456_add_sort_order_to_projects_table` —
  `unsignedInteger('sort_order')->nullable()->index()->after('status')`.
  Существующие записи получают `null` → уходят в хвост и сортируются по дате,
  то есть прежнее поведение сохраняется без бэкфилла.
- `Project::scopeOrdered()` — три ключа сортировки:
  `CASE WHEN sort_order IS NULL OR sort_order = 0 THEN 1 ELSE 0 END`
  (голова/хвост), `NULLIF(sort_order, 0)` (сам порядок),
  `published_at desc`.
- `ProjectsQuery::list()` — `orderByDesc('published_at')` → `ordered()`.
  Одна точка: покрывает `ProjectsBlock`, `ProjectsFull` (Livewire-список
  с категориями и пагинацией) и `RelatedThematic`.
- Filament: числовое поле `sort_order` в блоке настроек `ProjectForm`
  (рядом со статусом и категорией) + sortable-колонка в `ProjectsTable`.
  `defaultSort` таблицы намеренно оставлен `updated_at desc`.
- Переводы `panel.sort_order` / `panel.sort_order_hint` (ru + en).
- Тест `ProjectsQueryTest::test_list_sorts_by_sort_order_then_by_published_at`.

## Грабли

Второй ключ сортировки — именно `NULLIF(sort_order, 0)`, а не `sort_order`.
С обычным `orderBy('sort_order')` в хвостовой группе MySQL ставит `NULL`
раньше `0`, и дата публикации перестаёт решать порядок внутри хвоста.
`NULLIF` приравнивает `0` к `null`, после чего вся хвостовая группа
сортируется по дате. Тест ловит именно этот случай.

Решение по имени колонки: `sort_order`, не `order` — `order` зарезервирован
в MySQL и требует бэктиков в raw-выражениях.
