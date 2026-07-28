# Шаг 06. Filament-ресурс `MediaFileResource`

## Концепт

Стандартная тройка Filament-ресурса под нашу конвенцию:
- `MediaFileResource.php`
- `Pages/{List,Create,Edit}MediaFile.php`
- `Schemas/MediaFileForm.php`
- `Tables/MediaFilesTable.php`

Плюс регистрация группы `Медиа` в `AdminPanelProvider`.

## Что делаем

1. **Регистрация группы навигации.** В
   `app/Providers/Filament/AdminPanelProvider.php:62-67` — добавить
   строку `'Медиа'` в массив `navigationGroups([...])` перед
   `'Настройки'`:
   ```php
   ->navigationGroups([
       'Публикации',
       'Страницы',
       'Формы',
       'Медиа',       // NEW
       'Настройки',
   ])
   ```

2. **Создать структуру папок:**
   ```
   app/Filament/Resources/MediaFiles/
   ├── MediaFileResource.php
   ├── Pages/
   │   ├── CreateMediaFile.php
   │   ├── EditMediaFile.php
   │   └── ListMediaFiles.php
   ├── Schemas/
   │   └── MediaFileForm.php
   └── Tables/
       └── MediaFilesTable.php
   ```

3. **`MediaFileResource.php`** — из промта, **с правками под проект**:
   - Добавить трейт: `use App\Filament\Support\RoleAccessResource;`
     и `use RoleAccessResource;` внутри класса.
   - Добавить метод:
     ```php
     protected static function allowedRoles(): array
     {
         return [UserRole::Admin, UserRole::Editor, UserRole::Viewer];
     }
     ```
   - Добавить `use App\Enums\UserRole;` в шапку.
   - Добавить метод:
     ```php
     public static function getNavigationGroup(): ?string
     {
         return 'Медиа';
     }
     ```
     (без `__()` — совпадает со стилем других ресурсов проекта, где
     навигационная группа — строка из хардкода).
   - `$navigationSort = 90` — оставить как в промте.

4. **`Pages/ListMediaFiles.php`, `CreateMediaFile.php`, `EditMediaFile.php`**
   — из промта дословно.

5. **`Schemas/MediaFileForm.php`** — из промта дословно.

6. **`Tables/MediaFilesTable.php`** — из промта дословно. Внимание к
   `copy_url` action — `addslashes()` пре-существующий приём для JS-
   строки. Оставляем как есть.

## Файлы

- **EDIT** `app/Providers/Filament/AdminPanelProvider.php` — добавить
  `'Медиа'` в `navigationGroups`.
- **NEW** `app/Filament/Resources/MediaFiles/MediaFileResource.php`
- **NEW** `app/Filament/Resources/MediaFiles/Pages/ListMediaFiles.php`
- **NEW** `app/Filament/Resources/MediaFiles/Pages/CreateMediaFile.php`
- **NEW** `app/Filament/Resources/MediaFiles/Pages/EditMediaFile.php`
- **NEW** `app/Filament/Resources/MediaFiles/Schemas/MediaFileForm.php`
- **NEW** `app/Filament/Resources/MediaFiles/Tables/MediaFilesTable.php`

## References

- Структура ресурса — `app/Filament/Resources/Posts/PostResource.php`
  как эталон конвенций проекта.
- `RoleAccessResource` — `app/Filament/Support/RoleAccessResource.php`.
- `UserRole` — `app/Enums/UserRole.php`.
- Хардкод-строки групп — [architecture.md](../../architecture.md#точки-входа).
- Ключи локализации — [08-localization.md](08-localization.md).
- Хелпер `generate_uploaded_file_name` — [05-helper.md](05-helper.md).

## Gotchas

- Автопоиск ресурсов подхватывает всё из `app/Filament/Resources/` —
  дополнительная регистрация не нужна.
- В форме используется `Fieldset::make('upload')->label(__('panel.media_upload'))`
  — `Fieldset` в Filament 4 живёт в `Filament\Schemas\Components\Fieldset`,
  namespace в промте правильный.
- `TextEntry` в форме — это infolist-компонент внутри Schema (Filament 4
  унифицировал). Импорт `Filament\Infolists\Components\TextEntry`
  корректен в v4.
- `SelectFilter::make('type')->options(MediaFileType::class)->multiple()`
  — работает потому что enum реализует `HasLabel`.
- Действие `copy_url` использует `$tooltip()` из Alpine helpers Filament
  — уже доступен в контексте таблицы. Дополнительная регистрация не
  нужна.

## Checklist

- [ ] `navigationGroups` в `AdminPanelProvider` содержит `'Медиа'`.
- [ ] Все файлы созданы в правильных namespace.
- [ ] `RoleAccessResource` подключён, `allowedRoles` возвращает 3 роли.
- [ ] `getNavigationGroup()` возвращает `'Медиа'`.
- [ ] `/admin/media-files` открывается (после того как сделаны шаги 01–05).
- [ ] Список пустой (данных ещё нет — заполнится на шаге 09
      через `media:sync`).
- [ ] Кнопки Create / Edit / Delete видны.
- [ ] `pint --dirty` без замечаний.
