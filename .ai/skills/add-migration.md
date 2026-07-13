# Добавить миграцию

## Генерация
```bash
vendor/bin/sail artisan make:migration create_foo_table --no-interaction
vendor/bin/sail artisan make:migration add_bar_to_foo_table --no-interaction
```

## Правила именования
Изменения существующих таблиц — отдельные миграции с говорящим именем:
- `add_x_to_y_table`
- `change_x_type_on_y_table`
- `drop_x_from_y_table`
- `remove_x_from_y_and_z_tables` — только если действительно про обе таблицы

См. `database/migrations/` — там уже такой стиль.

## Применение / откат
```bash
vendor/bin/sail artisan migrate
vendor/bin/sail artisan migrate:rollback
vendor/bin/sail artisan migrate:fresh --seed   # только локально
```

## Что не забыть
- Обновить `$fillable` / `$casts` в модели. Особенно `$casts` для новых
  enum-полей (`'status' => PostStatus::class`).
- Если поле индексируется (`slug`, `published_at`, `status`) — добавить индексы
  сразу в миграции.
- Если контент попадает в публичный кэш — модель должна флашить нужные теги в
  `booted()` (см. `app/Models/Post.php`).
- Для JSON-колонок с блоками (`top_section`, `main_section`, `bottom_section`) —
  тип `longText` или `json`, cast — `'array'`.
- Обратимость: `down()` должен корректно откатывать, если это разумно.

## Тесты
Тестовая БД — отдельная (`DB_DATABASE=testing`, см. `phpunit.xml`). Миграции
автоматически подтягиваются перед тестами, если используется
`RefreshDatabase`/`DatabaseMigrations` в тестах.