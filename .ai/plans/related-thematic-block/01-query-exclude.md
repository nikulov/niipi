# 01 — `excludeId` в NewsQuery/ProjectsQuery

## Concept

Renderer нового блока должен исключать сам текущий Post/Project из
выдачи «связанного». Наиболее чистый способ — дать `NewsQuery::list()`
и `ProjectsQuery::list()` необязательный параметр `?int $excludeId = null`.
Все существующие вызовы продолжают работать без изменений.

## What we do

Добавить последний параметр `?int $excludeId = null` в оба метода `list()`:

```php
public function list(
    int $perPageOrLimit = 4,
    ?array $categoryIds = null,
    bool $paginate = false,
    string $pageName = 'page',
    ?int $excludeId = null,
): Collection|LengthAwarePaginator
```

Внутри: `->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))`
после `where status` и до `orderBy`.

## Files

- **EDIT** `app/Services/NewsQuery.php`
- **EDIT** `app/Services/ProjectsQuery.php`

## References

- `app/Services/NewsQuery.php` — текущая сигнатура
- `app/Services/ProjectsQuery.php` — текущая сигнатура
- Все callsite'ы (не должны ломаться):
    - `app/Blocks/Renderers/NewsBlockRenderer.php:22`
    - `app/Blocks/Renderers/ProjectsBlockRenderer.php:22`
    - `app/Livewire/Components/NewsFull.php:91`
    - `app/Livewire/Components/ProjectsFull.php` (аналогично)

## Gotchas

- Параметр строго **последний** — иначе ломаем positional-вызовы.
- `->when($excludeId, ...)` — не `if ($excludeId !== null)`, чтобы не
  фильтровать при `excludeId = 0` (id 0 не бывает в этой базе, но
  всё равно).

## Checklist

- [ ] `NewsQuery::list()` принимает `?int $excludeId = null`
- [ ] `ProjectsQuery::list()` принимает `?int $excludeId = null`
- [ ] Существующие вызовы работают (проверка — тесты `NewsQueryTest`/`ProjectsQueryTest` если есть, иначе прогон интеграционных)
- [ ] `sail bin pint --dirty --format agent`
