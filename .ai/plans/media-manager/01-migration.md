# Шаг 01. Миграция

## Концепт

Две таблицы:
- `media_files` — реестр загруженных файлов (path unique, disk, filename,
  mime_type, size, type, title, alt, uploaded_by).
- `media_file_usages` — пивот: `media_file_id` + `morphs('usable')` +
  `field`. Уникальность по кортежу.

## Что делаем

1. Сгенерировать миграцию:
   ```bash
   vendor/bin/sail artisan make:migration create_media_files_table --no-interaction
   ```
2. Заменить содержимое на код из
   [_source-prompt.md](_source-prompt.md#шаг-1-миграция) (обе таблицы в
   одной миграции — `up()` создаёт `media_files` затем
   `media_file_usages`; `down()` — в обратном порядке).
3. `vendor/bin/sail artisan migrate`.

## Файлы

- **NEW** `database/migrations/YYYY_MM_DD_HHMMSS_create_media_files_table.php`.

## References

- Соглашение по именам миграций — [conventions.md](../../conventions.md#миграции).
- Существующие миграции — `database/migrations/`.

## Gotchas

- `startingValue(1001)` — стилистика проекта. Проверь, что нет
  конфликта id с уже существующими таблицами (обе таблицы новые —
  безопасно).
- `->foreignId('uploaded_by')->constrained('users')->nullOnDelete()` —
  корректно ссылается на `users`.
- `morphs('usable')` создаёт `usable_type` (string) + `usable_id`
  (unsignedBigInteger) + композитный индекс. Не добавляй свои индексы
  вручную.
- Уникальный индекс `(media_file_id, usable_type, usable_id, field)`
  имеет длинное имя — задан явно как `media_file_usages_unique` (MySQL
  лимит 64 символа).

## Checklist

- [ ] Миграция создана в правильной директории.
- [ ] Код скопирован из промта без изменений.
- [ ] `sail artisan migrate` проходит.
- [ ] `sail artisan migrate:rollback` работает (откатывает обе таблицы).
- [ ] `sail artisan migrate` повторно — таблицы созданы заново.
