# Шаг 09. Подключение трейта к моделям + первичная индексация

## Концепт

Инфраструктура готова. Прикрепляем `TracksMediaUsage` к пяти моделям,
которые реально хранят пути к файлам, и запускаем `media:sync` — он
проиндексирует диск и соберёт все существующие usages.

## Что делаем

1. В каждой из моделей добавить `use App\Models\Concerns\TracksMediaUsage;`
   в шапку и `use TracksMediaUsage;` внутри класса рядом с уже
   существующими трейтами:
   - `app/Models/Page.php`
   - `app/Models/Post.php`
   - `app/Models/Project.php`
   - `app/Models/Footer.php`
   - `app/Models/GlobalSetting.php`

2. Запустить полный синк:
   ```bash
   vendor/bin/sail artisan storage:link  # если ещё не сделан
   vendor/bin/sail artisan media:sync
   ```

3. **Опционально (в рамках этого же шага):** добавить `hintAction` в
   самые «горячие» `FileUpload`:
   - `app/Filament/Resources/Posts/Schemas/PostForm.php` —
     `FileUpload::make('thumbnail')` → добавить
     `->hintAction(MediaPickerAction::make('thumbnail', imagesOnly: true))`.
   - Аналогично в `Projects/Schemas/ProjectForm.php`.
   - `app/Filament/Components/Gallery.php` — `FileUpload::make('urls')` с
     `multiple` → `MediaPickerAction::make('urls', imagesOnly: true, multiple: true)`.

   Полный обход всех `FileUpload::make()` — **вне рамок этого плана**
   (см. Boundaries в [README.md](README.md)).

## Файлы

- **EDIT** `app/Models/Page.php`
- **EDIT** `app/Models/Post.php`
- **EDIT** `app/Models/Project.php`
- **EDIT** `app/Models/Footer.php`
- **EDIT** `app/Models/GlobalSetting.php`
- **EDIT** `app/Filament/Resources/Posts/Schemas/PostForm.php` (опц.)
- **EDIT** `app/Filament/Resources/Projects/Schemas/ProjectForm.php` (опц.)
- **EDIT** `app/Filament/Components/Gallery.php` (опц.)

## References

- Список моделей — [domain.md](../../domain.md#сущности-appmodels).
- Существующие `FileUpload::make(...)` — см. grep-результат в
  `01. Analysis` шага (grep `FileUpload::make` по `app/`).
- `Post::booted()` уже флашит теги `['news','categories']` — не путать с
  media-трейтом; они складываются, оба остаются.

## Gotchas

- **Single-row модели `Footer` и `GlobalSetting`** сохраняются редко и
  синк на `saved` для них — почти no-op по стоимости.
- **`Footer` уже имеет свой `booted()`** с `Cache::forget('footer.data')`.
  Трейт `TracksMediaUsage::bootTracksMediaUsage` вешает
  дополнительные `static::saved`/`static::deleted` — Laravel корректно
  сложит оба слушателя (не заменит). Проверять специально не надо.
- **`GlobalSetting` — тот же случай.**
- **`Post` и `Project` тоже имеют собственный `booted()`.** Аналогично
  — оба слушателя срабатывают.
- **Menu не подключаем** — JSON `top_items`/`footer_items` содержит
  `url`, `label`, `page_slug`, `blank` — не пути к файлам. Проверено
  ручным чтением `app/Models/Menu.php`.
- **User** не подключаем — файловых полей нет.
- **Form / FormField / FormSubmission / FormSubmissionFile** — не
  подключаем. Причины:
  - `FormSubmission.data` / `FormSubmissionFile.path` — вложения заявок,
    отдельная инфраструктура. Не надо их вносить в общую медиатеку.
  - `Form.user_mail_attachments` (JSON пути) — сейчас управляется
    Filament `FileUpload`. Файлы лежат в `forms/user-mail-attachments/`
    — эта папка **исключена в `MediaSyncCommand`** (см.
    [04-artisan-command.md](04-artisan-command.md), правка A). Если
    позже понадобится доступ этих файлов через медиатеку — можно
    открыть `forms/user-mail-attachments/` отдельно.
- Порядок важен: сначала все правки моделей, потом `media:sync`
  (иначе usages не соберутся полностью).
- `media:sync` идемпотентна — можно перезапускать сколько угодно.
- **Осторожно с удалением через админку** пока не смёржен пикер и
  usages не собраны: удаление физически удаляет файл. Правило: сначала
  запустить `media:sync`, потом удалять.

## Checklist

- [ ] Трейт добавлен во все 5 моделей.
- [ ] `sail artisan media:sync` прошла без ошибок.
- [ ] В `/admin/media-files` список заполнен, у части файлов колонка
      «Использований» > 0.
- [ ] Тест: открыть любой Post, сохранить (без изменений) → количество
      усашей у файла-thumbnail остаётся тем же (не дублируется).
- [ ] Тест: удалить Post → usages для него удаляются, `MediaFile`
      остаётся с уменьшенным счётчиком.
- [ ] `pint --dirty` без замечаний.
