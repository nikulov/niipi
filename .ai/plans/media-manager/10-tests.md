# Шаг 10. Тесты

## Концепт

Проект использует PHPUnit 11 (не Pest) с `tests/Unit` и `tests/Feature`.
Тестовая БД — `DB_DATABASE=testing`, драйверы синхронные.
Покрываем ключевую бизнес-логику: сервис-синк, команду, resource smoke.

## Что делаем

### `tests/Unit/Services/MediaUsageServiceTest.php`

Тесты:
- `extract_paths_returns_paths_from_string_attribute` — модель с полем
  `thumbnail = 'media/x.png'` → `['thumbnail' => ['media/x.png']]`.
- `extract_paths_returns_paths_from_json_attribute` — модель с
  `top_section = [['data' => ['imageUrl' => 'media/x.jpg']]]` →
  находит `media/x.jpg` в `top_section`.
- `extract_paths_ignores_external_urls` — `https://example.com/x.jpg`
  не попадает.
- `extract_paths_ignores_strings_without_extension` —
  `heroicon-o-photo` не попадает.
- `sync_creates_media_file_and_usage_when_file_exists_on_disk` — с
  `Storage::fake('public')` кладём файл, сохраняем модель → создаётся
  `MediaFile` и `MediaFileUsage`.
- `sync_skips_paths_that_do_not_exist_on_disk` — путь есть в атрибуте,
  но файла на диске нет → ни `MediaFile`, ни `MediaFileUsage` не
  создаются.
- `sync_removes_stale_usages_and_adds_new_ones` — модель имеет старое
  использование file A, сохраняем с полем B → A удалено, B добавлено.
- `sync_is_idempotent` — двойное сохранение не дублирует usages.
- `remove_all_for_model_deletes_usages_but_not_media_file` — удаление
  модели → usages удаляются, MediaFile остаётся.

Использовать `Storage::fake('public')` в `setUp()`. **Модель** —
брать существующую `Footer` (single-row, поле `social_data` — JSON,
минимальный сет-ап) или `Page` (полноценные block-sections для JSON
extract-теста). У `User` нет файловых полей, `Post` тянет
`Post::booted()` c кэш-флашами. Наиболее чистый выбор — `Page`.

Fabrик у Page/Footer нет — создавать через `Model::create([...])` с
минимальным набором обязательных полей.

### `tests/Feature/Console/MediaSyncCommandTest.php`

- `it_indexes_existing_files_on_disk` — `Storage::fake('public')`, кладём
  3 файла (`images/x.jpg`, `gallery/y.png`, `files/z.pdf`), запускаем
  `media:sync`, проверяем что появились 3 `MediaFile`.
- `it_skips_livewire_tmp_files` — файл в `livewire-tmp/foo.png` не
  попадает.
- **`it_skips_forms_directory`** — файл в `forms/1/x.pdf` **НЕ** попадает
  (правка A из [04-artisan-command.md](04-artisan-command.md)); то же
  для `forms/user-mail-attachments/y.pdf`.
- `it_cleans_orphans` — создаём `MediaFile` без файла на диске,
  запускаем `media:sync` → запись удалена.
- `it_rebuilds_usages` — модель с трейтом, сохраняем с файлом → после
  `media:sync` в `media_file_usages` есть запись.
- `--usages-only_skips_file_scan` — новый файл на диске, запускаем
  `media:sync --usages-only` → файл НЕ индексируется.

### `tests/Feature/Filament/MediaFileResourceTest.php` (smoke + policy)

Проще всего повторить стиль
`tests/Feature/Filament/ResourceAccessTest.php` — HTTP-запросы к
`/admin/media-files` под разными ролями:
- `guest_is_redirected_to_login` — `get('/admin/media-files')`
  редирект на `/admin/login`.
- `admin_can_open_index` — с `$this->actingAs($user, 'web')` →
  `assertOk()`.
- `editor_can_open_index` — аналогично для `UserRole::Editor`.
- `viewer_can_open_index` — аналогично для `UserRole::Viewer`.

**Проверки политики** (`MediaFilePolicy`, см. [06b-policy.md](06b-policy.md)):
- `admin_can_delete_media_file` — вызов через
  `Livewire::test(EditMediaFile::class, ['record' => $id])->callAction(DeleteAction::class)`
  проходит; `Storage::disk('public')->missing($path)`.
- `editor_cannot_delete_media_file` — тот же вызов под Editor →
  `->assertActionHalted(DeleteAction::class)` (или проверить наличие
  кнопки: `->assertActionHidden(DeleteAction::class)`).
- `viewer_cannot_create_or_edit` — под Viewer открыть index, проверить
  что кнопки Create/Edit скрыты
  (`Livewire::test(ListMediaFiles::class)->assertActionHidden('create')`).

Регистрация группы `'Медиа'` в navigationGroups — уже покроется
`admin_can_open_index` (маршрут работает — панель отрендерилась
корректно). Отдельный тест не нужен.

**Опционально** добавить `/admin/media-files` в
`ResourceAccessTest::indexRoutes()` — тогда viewAny всех ролей
покроется уже существующим data-provider тестом.

## Файлы

- **NEW** `tests/Unit/Services/MediaUsageServiceTest.php`
- **NEW** `tests/Feature/Console/MediaSyncCommandTest.php`
- **NEW** `tests/Feature/Filament/MediaFileResourceTest.php`

## References

- Соглашения — [conventions.md](../../conventions.md#тесты).
- Существующие юнит-тесты — `tests/Unit/Services/` (пример стиля).
- Существующие feature-тесты — `tests/Feature/Livewire/*Test.php`.
- Запуск теста: `vendor/bin/sail artisan test --compact --filter=Media`.

## Gotchas

- `RefreshDatabase` требует, чтобы миграции были в `database/migrations/`
  — они там (шаг 01). Тесты автоматически поднимут схему.
- `Storage::fake('public')` изолирует диск на время теста — не трогает
  реальный `storage/app/public/`.
- Для трекинга `saved` события: в тесте после `Storage::fake`
  положить файл через `UploadedFile::fake()->image('x.png')`, потом
  сохранить модель с полем = сформированному пути. `syncForModel`
  вызовется автоматически из трейта.
- Для чистоты в анонимных моделях: если использовать реальные модели
  проекта (Page/Post) — не забыть про observers, которые тоже дёрнутся
  (`PageObserver` выставляет `published_at`). Не мешает, просто
  учитывать.
- Для HTTP-стиля Filament-тестов панель поднимается middleware'ом
  автоматически (см. `ResourceAccessTest`). Для Livewire-стиля с
  `Livewire::test(EditMediaFile::class, ...)` может понадобиться
  `Filament::setTenant(null)` / `Filament::setCurrentPanel(Filament::getPanel('admin'))`
  в setUp — добавить только если тест реально падает.
- `CACHE_STORE=array` в phpunit.xml — теговый кэш работает per-request,
  без Valkey. `MediaFile::deleted → Cache::forget(...)` вызовется, но
  реально ничего не сбросит (пусто). Достаточно для проверки, что
  вызов не бросает исключение.
- В тесте `extractPaths` для JSON-полей — использовать `Model::create([...])`
  с cast'ом `array`: Laravel сохранит его как JSON строку в БД, дальше
  `getAttributes()` вернёт RAW-строку, чего и ждёт `extractPaths`. Не
  использовать `->setRawAttributes()` — это обходит cast'ы.

## Checklist

- [ ] Все 3 файла тестов созданы.
- [ ] `sail artisan test --compact --filter=MediaUsageService` зелёный.
- [ ] `sail artisan test --compact --filter=MediaSyncCommand` зелёный.
- [ ] `sail artisan test --compact --filter=MediaFileResource` зелёный.
- [ ] Полный прогон `sail artisan test --compact` — без регрессий
      в существующих тестах.
