# Шаг 03. MediaUsageService

## Концепт

Сервис — единая точка синка. Публичный API:
- `syncForModel(Model)` — сравнить желаемые usages с существующими,
  добавить недостающие, удалить лишние. Вызывается из трейта в `saved`.
- `removeAllForModel(Model)` — удалить все usages модели. Вызывается из
  `deleted`.
- `extractPaths(Model)` — public для тестов; сканирует все атрибуты
  модели (строки + JSON) и возвращает `[field => [path, ...]]`.
- `registerFile(path, disk)` — публичная обёртка вокруг
  `findOrCreateMediaFile()` для использования из `media:sync`.

## Что делаем

1. Создать `app/Services/MediaUsageService.php` — код целиком из
   [_source-prompt.md](_source-prompt.md#шаг-5-сервис). В проекте
   ничего специфичного менять не надо.

## Файлы

- **NEW** `app/Services/MediaUsageService.php`

## References

- Стиль сервисов проекта — `app/Services/NewsQuery.php`, `ProjectsQuery.php`,
  `ContentRenderer.php`. Классы без интерфейсов, простые публичные
  методы. Совпадает с промтом.

## Gotchas

- `extractPaths()` использует `$model->getAttributes()` — это RAW
  значения без cast'а. Для JSON-полей (наш случай: `top_section`,
  `main_section`, `bottom_section`, `Menu.top_items` и т.д.) вернётся
  JSON-строка → `json_decode` → рекурсивный обход. Это специально —
  чтобы работать и со строками-путями, и с вложенными JSON.
- `looksLikeFilePath()` отсекает `http(s)://…` и требует расширение из
  allowlist. Внешние URL, heroicon-имена и мусор безопасно
  игнорируются.
- `findOrCreateMediaFile()` создаёт `MediaFile` **только если файл
  реально существует на диске** (`Storage::disk($disk)->exists($path)`) —
  фантомы (строки, случайно похожие на путь) не попадают в реестр.
- Diff по ключу `media_file_id:field` — если один и тот же файл лежит
  в двух разных полях (например, `thumbnail` и в блоке `main_section`),
  получатся два usages. Это ожидаемое поведение.
- `SKIP_ATTRIBUTES` включает стандартные (`id`, timestamps, `slug`,
  `password`, `remember_token`). Специфичных для NIiPI полей типа
  `sort`, `status`, `role` в списке нет — но они либо не строки
  (numeric/enum), либо строки без расширения, поэтому фильтр
  `looksLikeFilePath()` их отбрасывает.

## Checklist

- [ ] Сервис создан.
- [ ] `pint` без замечаний.
- [ ] Ручная проверка через tinker:
      ```
      sail artisan tinker --execute 'app(App\Services\MediaUsageService::class)
          ->extractPaths(App\Models\Post::first())'
      ```
      возвращает пустой массив или `[field => [path,...]]` в зависимости от
      контента.
