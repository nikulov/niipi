# Паттерн: RoleAccessResource на Filament ресурсах

Все Filament ресурсы, требующие ограничения по роли, подключают
`App\Filament\Support\RoleAccessResource` и объявляют разрешённые роли.

## Использование
```php
class PostResource extends Resource
{
    use RoleAccessResource;

    protected static function allowedRoles(): array
    {
        return [UserRole::Admin, UserRole::Editor, UserRole::Viewer];
    }
    // ...
}
```

## Правила
- **Все три роли** (`Admin`, `Editor`, `Viewer`) — для полного доступа к
  ресурсу. Для админ-only — только `Admin`.
- Enum — `App\Enums\UserRole`.
- Trait должен закрывать стандартные точки доступа Filament (`canViewAny`,
  `canCreate`, `canEdit`, `canDelete`). Смотри реализацию:
  `app/Filament/Support/RoleAccessResource.php`.

## Когда чего давать
Ориентир по проекту:
- `Admin` — все ресурсы.
- `Editor` — контент (Posts, Projects, Pages, Categories, Menus, Footers,
  Forms/FormSubmissions).
- `Viewer` — обычно read-only, но зависит от реализации trait'а — проверить.

## Не забывать
- Новый ресурс без trait'а виден всем аутентифицированным пользователям
  админки — это баг безопасности. Trait подключается сразу.