# Диски: `throw`, `report` и молчаливые сбои записи

Проверено прогонами 2026-08-21 на живом коде (Laravel 12 / Flysystem 3),
поводом был баг #23 (см. [plans/public-form-crashes](../plans/public-form-crashes/README.md)).

## Матрица поведения при неудачной записи

Сбой воспроизводится так: каталог назначения **существует**, но закрыт на
запись (0555) — это продовый случай: чужой владелец после деплоя, полный диск,
квота.

| `throw` | `report` | что вернёт `put()` / `putFileAs()` | запись в лог |
| --- | --- | --- | --- |
| `false` | `false` | `false` | нет |
| `false` | `true` | `false` | `error` |
| `true` | `false` | `UnableToWriteFile` | улетает в обработчик → 500 + запись |

Текущий конфиг всех дисков проекта — **первая строка**: сбой записи не виден
нигде. `'report' => true` даёт диагностику, ничего не меняя в поведении.

## `throw => false` глотает не всё

`FilesystemAdapter::put()` ловит **только** `UnableToWriteFile` и
`UnableToSetVisibility`:

```php
} catch (UnableToWriteFile|UnableToSetVisibility $e) {
    throw_if($this->throwsExceptions(), $e);
    $this->report($e);
    return false;
}
```

Остальные исключения Flysystem проходят насквозь независимо от флага. Например
`UnableToCreateDirectory` (в корне лежит файл с именем нужного каталога) даёт
500 и при `throw => false`. **Практический вывод:** если ловишь баг про
«молча не записалось» — это ровно один класс сбоев, «каталог есть, файл не
пишется». Проверять гипотезы надо на нём, иначе эксперимент покажет одинаковое
поведение при обоих значениях флага и вывод будет ложным (я на этом
споткнулся).

## `size()` кидает всегда

`FilesystemAdapter::size()` — единственный метод адаптера без `try/catch`, он
игнорирует `'throw' => false`. Соседние `mimeType()`, `checksum()`, `move()`
исключение ловят и возвращают `false`. Отсюда пятисотки в #23: правило
валидации `max:` зовёт `getSize()`.

## `TemporaryUploadedFile::storeAs()` выбрасывает результат `put()`

Livewire переопределяет `storeAs()` и **не смотрит**, что вернул `put()`:

```php
Storage::disk($disk)->put($newPath, $this->storage->readStream($this->path), $options);

return $newPath;   // путь возвращается всегда
```

Значит `$upload->store($dir, $disk)` **никогда не вернёт `false`** — проверять
его результат бессмысленно. При неудачной записи наверх уходит правдоподобный
путь к несуществующему файлу. Способы поймать: `'throw' => true` на диске
назначения (тогда `put()` кидает) либо явный `Storage::disk($disk)->exists($path)`
после `store()`. Живой случай — баг #42 в [plans/bugs.md](../plans/bugs.md).

## Проверка лога в тестах

`Log::spy()` в связке с `Storage::build()` даёт ложные срабатывания
`shouldHaveReceived('error')` — проверено контрольным прогоном, где запись
проходила успешно, а «лог» показывал наличие записи. Надёжный способ:

```php
Log::listen(function ($message) { $this->records[] = $message->level; });
```

Событие `MessageLogged` ловит и то, что пишет обработчик исключений через
`report()`.
