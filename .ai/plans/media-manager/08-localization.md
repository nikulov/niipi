# Шаг 08. Локализация

## Концепт

Все админ-строки в проекте живут в `lang/{locale}/panel.php` (см.
[file-map.md](../../file-map.md#lang)). Добавляем ключи `media_*` и
несколько общих.

## Что делаем

1. **`lang/ru/panel.php`** — добавить ключи в конец файла (или в
   логичную группу, если она уже структурирована):
   ```php
   'media_file' => 'Медиа-файл',
   'media_files' => 'Медиа-файлы',
   'media_files_list' => 'Медиа-файлы',
   'media_upload' => 'Загрузка',
   'media_file_info' => 'Информация о файле',
   'media_type_image' => 'Изображение',
   'media_type_document' => 'Документ',
   'media_type_other' => 'Другое',
   'media_used' => 'Используется',
   'media_used_in' => 'Используется в',
   'media_not_used' => 'Не используется',
   'media_usages_count' => 'Использований',
   'media_copy_url' => 'Скопировать URL',
   'media_url_copied' => 'URL скопирован',
   'media_confirm_delete_used' => 'Файл используется в:',
   'media_choose_from_library' => 'Выбрать из библиотеки',
   'media_picker_title' => 'Библиотека медиа',
   'media_no_files_found' => 'Файлы не найдены',
   'media_selected' => 'Выбрано',
   ```
2. **НЕ добавляй** следующие ключи — они **уже есть** в
   `lang/ru/panel.php` (проверено grep'ом): `search` (`Поиск`),
   `thumbnail` (`Миниатюра`), `file_name` (`Файл`),
   `mime_type` (`MIME тип`), `size` (`Размер`), `title` (`Заголовок`),
   `alt` (`Альтернативный текст`), `file` (`Файл`),
   `settings` (`Настройки`), `url` (`Ссылка`), `type` (`Тип`),
   `edit` (`Редактировать`), `delete` (`Удалить`),
   `created_at` (`Создано`).
   Добавляем **только** `media_*` ключи из списка выше.
3. **`lang/en/panel.php`** — те же ключи с английскими значениями.
   Если файл существует и используется — добавить. Если проект
   монолингвальный (только ru) — пропустить.

## Файлы

- **EDIT** `lang/ru/panel.php`
- **EDIT** `lang/en/panel.php` (если существует)

## References

- Все админ-строки — `__('panel.*')` ([conventions.md](../../conventions.md)).
- В ресурсах уже используются ключи типа `panel.new`, `panel.news`,
  `panel.publications` — стиль сохранить.

## Gotchas

- Слово `'type'` в промте указано отдельным ключом, но у нас может быть
  уже общий `'type'` (тип чего-то). Не переопределяй значение — просто
  используй существующее.
- Разделение `media_file` (единственное) и `media_files` (множественное)
  — соответствует `getModelLabel()` / `getPluralModelLabel()`.
- `media_files_list` — для `getNavigationLabel()`. У нас в других
  ресурсах шаблон `panel.{entity}_list` (`news_list`, `pages_list` и
  т.д.).

## Checklist

- [ ] Все ключи `media_*` добавлены в `lang/ru/panel.php`.
- [ ] Общие ключи (`file`, `title`, `edit`, ...) не задублированы.
- [ ] `en/panel.php` синхронизирован (если применимо).
- [ ] `/admin/media-files` показывает русские подписи (не raw ключи типа
      `panel.media_upload`).
