# Паттерн: BasePolicy + Admin bypass

Все политики моделей наследуют `App\Policies\BasePolicy`. Она задаёт два
инварианта:

1. **Admin bypass** через `before()`:
    ```php
    public function before(User $user, string $ability): ?bool
    {
        return $user->role === UserRole::Admin ? true : null;
    }
    ```
    Admin автоматически проходит все проверки без затрагивания per-action методов.
2. **Хелперы ролей** — `isEditor()`, `isViewer()`, `isEditorOrViewer()`.

## Пример реализации

`app/Policies/PostPolicy.php`:

```php
class PostPolicy extends BasePolicy
{
    public function viewAny(User $user): bool  { return $this->isEditorOrViewer($user); }
    public function view(User $user, Post $p): bool { return $this->isEditorOrViewer($user); }
    public function create(User $user): bool   { return $this->isEditor($user); }
    public function update(User $user, Post $p): bool { return $this->isEditor($user); }
    public function delete(User $user, Post $p): bool { return false; } // только Admin через before()
    // ...
}
```

## Регистрация

Политики подхватываются **автоматически** по конвенции
`App\Policies\{Model}Policy`. Массив `$policies` в `AuthServiceProvoider` — легаси
(остался от Laravel <=10), в текущем Laravel 12 не используется, поскольку
класс не расширяет `Illuminate\Foundation\Support\Providers\AuthServiceProvider`.

## Правило

- Новую модель → создать `App\Policies\{Model}Policy extends BasePolicy` рядом
  с существующими. Больше нигде регистрировать не надо.
- Никогда не запрещать что-то в `before()` — только разрешать (`true`), иначе
  сломается Admin bypass.
- Смотри также [role-access-resource.md](role-access-resource.md) — видимость
  ресурса в админке (это отдельный слой от политики).
