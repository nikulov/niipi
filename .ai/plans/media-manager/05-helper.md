# Шаг 05. Хелпер `generate_uploaded_file_name`

## Концепт

Единая функция для генерации имени сохраняемого файла:
`{slug-16-имени}-{timestamp}.{ext}`. Используется в
`FileUpload::getUploadedFileNameForStorageUsing()`.

## Что делаем

1. Дописать функцию в существующий `app/helpers.php`. Файл уже
   подключается через `bootstrap/app.php:7`, `composer.json` править
   НЕ надо.
2. Код — из [_source-prompt.md](_source-prompt.md#шаг-7-helper-для-имён-загружаемых-файлов).
3. `use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;` —
   корректный namespace для Livewire 3.7.

## Файлы

- **EDIT** `app/helpers.php` — добавить функцию `generate_uploaded_file_name()`
  и `use` в шапке.

## References

- Существующий `public_asset()` в том же файле — как образец стиля
  (обёртка в `if (! function_exists(...))`).
- Пример живого использования той же логики (inline) —
  `app/Filament/Resources/Posts/Schemas/PostForm.php:102-107` и
  аналоги во всех `FileUpload::make()`.

## Gotchas

- В существующих `FileUpload` этот closure дублируется 10+ раз. Замена
  на хелпер — **вне рамок этого плана** (см. Boundaries в README).
  При желании — отдельным cleanup-PR после того, как медиа-менеджер
  вмёржен.
- `str()->slug()->limit(20)` может дать пустую строку, если имя
  состоит из не-ASCII без совпадений транслитерации. Тогда результат
  — просто `-{timestamp}.ext`. Не проблема, но помни.

## Checklist

- [ ] Функция определена в `app/helpers.php`.
- [ ] `use` добавлен.
- [ ] Проверка в tinker:
      `sail artisan tinker --execute 'dump(function_exists("generate_uploaded_file_name"))'`
      → `true`.
- [ ] `pint --dirty` без замечаний.
