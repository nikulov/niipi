# Шаг 06b. MediaFilePolicy

## Концепт

В проекте строгая конвенция: у каждой модели своя политика
`App\Policies\{Model}Policy` extends `BasePolicy`. Laravel 12
автоподхватывает по имени. `BasePolicy::before()` даёт Admin bypass
через `role === UserRole::Admin`.

**Без своей политики** Filament даёт полный доступ всем ролям,
прошедшим `canViewAny()`. Viewer сможет удалять — это не по паттерну
проекта (см. `PostPolicy::delete` = false даже для Editor).

## Что делаем

Создать `app/Policies/MediaFilePolicy.php` по образцу `PostPolicy`.

```php
<?php

namespace App\Policies;

use App\Models\MediaFile;
use App\Models\User;

class MediaFilePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isEditorOrViewer($user);
    }

    public function view(User $user, MediaFile $mediaFile): bool
    {
        return $this->isEditorOrViewer($user);
    }

    public function create(User $user): bool
    {
        return $this->isEditor($user);
    }

    public function update(User $user, MediaFile $mediaFile): bool
    {
        return $this->isEditor($user);
    }

    public function delete(User $user, MediaFile $mediaFile): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, MediaFile $mediaFile): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
```

## Файлы

- **NEW** `app/Policies/MediaFilePolicy.php`

## References

- Эталон конвенции — `app/Policies/PostPolicy.php`.
- База — `app/Policies/BasePolicy.php` (Admin bypass через `before()`).
- Конвенция и автоподхват — [conventions.md](../../conventions.md#авторизация)
  + [patterns/base-policy.md](../../patterns/base-policy.md).
- Автопоиск: в Laravel 12 политики находятся по имени
  `App\Policies\{Model}Policy`. Массив `$policies` в
  `AuthServiceProvoider` — мёртвый код (см. заметку в
  `.ai/file-map.md`).

## Матрица прав

| Роль    | viewAny | view | create | update | delete |
|---------|:-------:|:----:|:------:|:------:|:------:|
| Admin   |    ✓    |  ✓   |   ✓    |   ✓    |   ✓    |
| Editor  |    ✓    |  ✓   |   ✓    |   ✓    |   ✗    |
| Viewer  |    ✓    |  ✓   |   ✗    |   ✗    |   ✗    |

Admin — единственный, кто может физически удалять файл с диска.
Editor может загружать новые и редактировать метаданные (title/alt).
Viewer — только просмотр и копирование URL.

## Gotchas

- Политика **не** влияет на видимость ресурса в навигации — за это
  отвечает `RoleAccessResource::shouldRegisterNavigation()`
  (`allowedRoles = [Admin, Editor, Viewer]`). Мы сохраняем список
  всех трёх ролей, чтобы Viewer вообще увидел пункт меню.
- `canViewAny()` из `RoleAccessResource` возвращает true для всех
  разрешённых ролей. Но Filament затем ВСЁ РАВНО прогоняет через
  `viewAny()` политики. Наша политика тоже разрешает — совпадает.
- Если в будущем захотим дать Admin+Editor права на удаление, но
  оставить Viewer read-only — поменять `delete` на `$this->isEditor($user)`.
- В `Tables/MediaFilesTable.php` кнопки `EditAction` / `DeleteAction`
  автоматически скроются для ролей без соответствующих прав. Никаких
  ручных `->visible(fn () => ...)` не надо.

## Checklist

- [ ] Файл создан по образцу `PostPolicy`.
- [ ] Матрица прав соответствует таблице выше.
- [ ] Тест: Viewer открывает `/admin/media-files` → видит список, но
      НЕ видит кнопки Create/Edit/Delete.
- [ ] Тест: Editor открывает → видит Create/Edit, но НЕ видит Delete.
- [ ] Тест: Admin видит всё, может удалить.
- [ ] `pint --dirty` без замечаний.