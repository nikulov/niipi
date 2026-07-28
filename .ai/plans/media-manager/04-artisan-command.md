# Шаг 04. Артизан-команда `media:sync`

## Концепт

Двухфазная команда:
1. **Скан диска → БД:** обход `Storage::disk('public')->allFiles()`,
   `findOrCreateMediaFile` на каждом. Пропускает `livewire-tmp/*` и
   dot-файлы.
2. **Чистка сирот:** удаление `MediaFile`-записей, чей файл не найден
   на диске (каскадом удаляются usages).
3. **Пересборка usages:** обход всех классов моделей с трейтом
   `TracksMediaUsage` → `syncForModel` в chunk'ах.

Флаг `--usages-only` пропускает фазы 1 и 2.

## Что делаем

1. Создать `app/Console/Commands/MediaSyncCommand.php` — код из
   [_source-prompt.md](_source-prompt.md#шаг-6-артизан-команда), но с
   **тремя обязательными правками под наш проект.**
2. Регистрация не нужна — Laravel 12 автоматически подхватывает команды
   из `app/Console/Commands/` (см. `routes/console.php` — там пусто
   кроме `inspire`).

## Правки против оригинального промта

### Правка A — исключить папку `forms/` из скана

В `storage/app/public/forms/` лежат вложения заявок
(`FormSubmissionFile`) и вложения писем форм
(`Form.user_mail_attachments`). Индексация даёт admin'у возможность
удалить их из медиа-библиотеки — потеря вложений заявок.

В `scanFiles()` заменить блок пропуска на:
```php
$skipPrefixes = ['livewire-tmp/', 'forms/'];

foreach ($files as $path) {
    $skip = str_starts_with(basename($path), '.');
    foreach ($skipPrefixes as $prefix) {
        if (str_starts_with($path, $prefix)) {
            $skip = true;
            break;
        }
    }
    if ($skip) {
        $bar->advance();
        continue;
    }
    // ... остальная логика findOrCreate
}
```

### Правка B — стабильная пагинация в `chunk()`

В `rebuildUsages()` добавить `orderBy('id')` для каждого chunk-запроса
(без явной сортировки Laravel эмитит warning на MySQL 8+):
```php
$class::query()->orderBy((new $class)->getKeyName())->chunk(100, function ($models) use ($service) {
    foreach ($models as $model) {
        $service->syncForModel($model);
    }
});
```

### Правка C — более устойчивый фильтр `Concerns/`

Оригинальная проверка `$file->getRelativePath() === 'Concerns'` работает
только для однослойной вложенности. Наш проект такой и есть (только
`Models/Concerns/HasSectionOptions.php`), но правило сделать
устойчивее:
```php
if (str_starts_with($file->getRelativePath(), 'Concerns')) continue;
```
Это не спасает от абстрактных классов (у нас их в `Models/` нет), но
дополнительно защитит проверка `class_exists()`, которая уже стоит.

## Файлы

- **NEW** `app/Console/Commands/MediaSyncCommand.php`

## References

- Метод сканирования моделей через `File::allFiles(app_path('Models'))`
  + `class_uses_recursive` — стандартный паттерн. Каталог `Concerns/`
  явно пропускается.
- Пример работы с командой — `routes/console.php` (только `inspire`;
  других кастомных нет).

## Gotchas

- Уже проиндексированные файлы пропускаются через `MediaFile::where('path', $path)`
  — команда идемпотентна.
- Root-level файлы в `storage/app/public/` (типа
  `5KEflU2ervweMd76vzNZrBBrdnTAnftI1TkgPelZ.jpg` — legacy `Storage::putFile`)
  будут проиндексированы с `usages_count = 0`. Это OK: admin увидит их
  в библиотеке, может удалить.
- Chunk 100 моделей — OK для CMS-объёмов (посты/страницы/проекты
  максимум сотни).
- Первый запуск на существующем storage может занять минуту-две в
  зависимости от количества файлов. Прогресс-бар покажет.
- `syncForModel` пишет в `media_file_usages` (отдельная таблица), не
  меняет исходную модель — chunk безопасен.
- Observers Page/Post/Project (`published_at = now()` на первой
  публикации) не срабатывают при `chunk() + save`, потому что мы не
  вызываем save на модели. Мы только читаем и пишем в
  `media_file_usages` через сервис — модели не сохраняются, observers
  не дёргаются. ОК.

## Checklist

- [ ] Команда создана с правками A, B, C.
- [ ] `sail artisan list media` показывает `media:sync`.
- [ ] `sail artisan media:sync --help` показывает опции.
- [ ] Первый запуск на dev-БД: файлы из `images/`, `gallery/`, `files/`
      проиндексированы; файлы из `forms/` — **НЕ** проиндексированы
      (проверить `MediaFile::where('path', 'like', 'forms/%')->count() === 0`).
- [ ] Повторный запуск: 0 создано, 0 удалено (идемпотентность).
- [ ] `sail artisan media:sync --usages-only` пропускает скан диска,
      только пересобирает usages.
- [ ] Никаких warning в логах про `LazyCollection`/pagination без
      `orderBy`.
