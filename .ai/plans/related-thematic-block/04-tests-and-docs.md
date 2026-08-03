# 04 — Тесты и документация

## Concept

Покрыть renderer юнит-тестами (по образцу существующих тестов в
`tests/Unit/Blocks/Renderers/`) и обновить `.ai/` — file-map, domain,
skills если нужно.

## What we do

### Тесты

Файл `tests/Unit/Blocks/Renderers/RelatedThematicRendererTest.php`.

Сценарии:

1. **Не-Post/Не-Project** → `render()` возвращает `''`.
2. **Post без `categoryIds`, у поста есть категории** → используются
   категории поста; в выборке — только опубликованные посты этих
   категорий; текущий пост исключён.
3. **Post с `categoryIds`** → игнорируются категории поста, используются
   заданные.
4. **Post без категорий и без `categoryIds`** → `''`.
5. **Project — те же 4 сценария** (можно с датапровайдером).
6. **`limit`** кламппится (0 → 1, 999 → 20).
7. **Кнопка URL**: у поста с категориями `['tech', 'other']` btnUrl
   содержит `newsCategory=tech`. Для Project — `projectsCategory=tech`.

Использовать существующие фабрики (если есть) или создавать модели
руками через `Post::create(...)` — фабрик пока не видел, посмотреть
как сделано в `NewsBlockRendererTest.php` при наличии.

Если у `NewsQuery`/`ProjectsQuery` уже есть тесты — прогнать их после
изменения сигнатуры.

### Документация

- **`.ai/file-map.md`** — в списке блоков (`app/Blocks/Renderers/`)
  добавить `RelatedThematic`.
- **`.ai/domain.md`** — в разделе «Блочный контент» упомянуть, что
  Post/Project при создании автоматически получают в main-секцию
  `related-thematic` + `category-list`.
- **`.ai/skills/add-block.md`** — обновлять **не нужно** (рецепт
  общий, не про конкретный блок).
- **`.ai/plans/plan.md`** — переместить строку из «Active» в «Wrapping up»
  → потом в «Archive» после коммита.

### Финализация плана

После коммита:

1. Строка в `plan.md` → «Archive».
2. `plans/related-thematic-block/` → удалить.
3. Создать одностраничный итог `plans/archived/related-thematic-block.md`
   (goal, что сделано, коммит, дата).

## Files

- **NEW** `tests/Unit/Blocks/Renderers/RelatedThematicRendererTest.php`
- **EDIT** `.ai/file-map.md`
- **EDIT** `.ai/domain.md`
- **EDIT** `.ai/plans/plan.md`
- Позже: **NEW** `.ai/plans/archived/related-thematic-block.md`

## References

- `tests/Unit/Blocks/Renderers/` — существующие тесты рендереров
- `.ai/checklists/before-commit.md` — обязательные шаги перед коммитом
- `.ai/workflow.md` — команды pint/npm/test через Sail

## Gotchas

- Тесты не мокают БД (по глобальному правилу — интеграционные тесты
  бьют в реальную БД `testing`).
- `sail artisan test --compact --filter=RelatedThematicRenderer` —
  фильтровать по одному тесту при отладке.

## Checklist

- [ ] Тесты написаны и зелёные
- [ ] Прогнан весь `tests/Unit/Blocks/Renderers/` — регрессий нет
- [ ] `.ai/file-map.md` обновлён
- [ ] `.ai/domain.md` обновлён
- [ ] `.ai/plans/plan.md` двигается по стадиям
- [ ] `sail bin pint --dirty --format agent`
- [ ] `.ai/checklists/before-commit.md` — все пункты пройдены
