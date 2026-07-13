# Паттерн: RoleAccessResource на Filament ресурсах

Filament-ресурсы объявляют роли, которым видна их навигация и виден сам ресурс,
через trait `App\Filament\Support\RoleAccessResource`. Реальная авторизация
действий (create/update/delete/…) закрывается **политиками** — см. ниже.

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

## Что реально делает trait
Смотри `app/Filament/Support/RoleAccessResource.php` — переопределяет только:
- `shouldRegisterNavigation()` — прячет пункт меню, если у юзера роль вне `allowedRoles()`.
- `canViewAny()` — блокирует список записей ресурса.

Дефолт `allowedRoles(): [UserRole::Admin]` — ресурс без переопределения виден
только Admin.

## Авторизация действий — Policies
Все остальные проверки (`create`, `update`, `delete`, `deleteAny`, ...) идут через
классы в `app/Policies/`. Они наследуются от `App\Policies\BasePolicy`:

```php
public function before(User $user, string $ability): ?bool
{
    return $user->role === UserRole::Admin ? true : null; // Admin bypass
}
```

Дальше методы политики опираются на `isEditor()` / `isViewer()` /
`isEditorOrViewer()`. Пример `App\Policies\PostPolicy`:
- `viewAny`/`view` → editor или viewer
- `create`/`update` → editor
- `delete`/`deleteAny`/`forceDelete*` → всегда false (только Admin через `before()`).

Политики автоподхватываются Laravel по конвенции `App\Policies\{Model}Policy`.
Массив `$policies` в `App\Providers\AuthServiceProvoider` — легаси и не работает
(класс не расширяет `Illuminate\Foundation\Support\Providers\AuthServiceProvider`),
оставлен как памятка.

## Реальная роль-матрица (staging)
| Ресурс | allowedRoles() |
|---|---|
| Post, Project, Category | Admin, Editor, Viewer |
| Page, User, Form, FormSubmission | Admin, Viewer |
| Menu, Footer, GlobalSetting | Admin only |

Viewer видит контент, но политики закрывают запись. Editor реально имеет право
писать только по Post/Project (см. соответствующие политики).

## Не забывать
- Новый ресурс без trait'а показывается всем и по умолчанию доступен всем
  ролям — обязательно подключить trait и указать `allowedRoles()`.
- Для реального ограничения create/update/delete — писать соответствующую
  политику (или пусть отработает `BasePolicy::before()` для Admin).
