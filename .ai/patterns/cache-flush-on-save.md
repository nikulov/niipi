# Паттерн: Флаш кэша по тегам при saved/deleted

Публичные модели контента (Post/Project и т.п.) флашат теги кэша при сохранении
и удалении. Пример — `app/Models/Post.php`:

```php
protected static function booted(): void
{
    $flush = function (): void {
        foreach (self::cacheTags() as $tags) {
            cache()->tags($tags)->flush();
        }
    };

    static::saved($flush);
    static::deleted($flush);
}

private static function cacheTags(): array
{
    return [
        ['news', 'categories'],
    ];
}
```

## Замечания
- Драйвер кэша **должен поддерживать теги**. В проекте это Valkey (Redis-совместимый).
- Массив в массиве — каждая внутренняя пачка передаётся в `cache()->tags(...)`.
  Порядок вложенности сохраняет возможность иметь несколько независимых
  тег-групп для одной модели.
- Ставить `saved`, а не `updated` — чтобы флаш срабатывал и при создании.
- Для сущностей с зависимостями (например, `Category` влияет на списки постов)
  дублировать пересекающиеся теги в обоих моделях.

## Когда добавлять
- Модель попадает на публичный маршрут (`ContentController`).
- Её выборки идут через сервисы (`NewsQuery`, `ProjectsQuery`), где потенциально
  включён кэш.
- Если модели админ-only (`GlobalSetting` для управления, `FormSubmission`
  для приёма заявок), — обычно не требуется.