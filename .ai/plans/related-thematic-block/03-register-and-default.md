# 03 — Регистрация и вставка по умолчанию

## Concept

Подключить новый блок в оба реестра (рендер + Filament) и добавить в
дефолтный набор main-секции для Post и Project — перед уже
существующим дефолтом `CategoryList`.

## What we do

### `app/Blocks/BlockRenderRegistry.php`

Добавить import и запись в `map()`:

```php
use App\Blocks\Renderers\RelatedThematicRenderer;
// ...
RelatedThematicRenderer::key() => RelatedThematicRenderer::class,
```

### `app/Filament/Components/BlockRegistry/BlockRegistry.php`

- Импорт `use App\Filament\Components\RelatedThematic;`
- В `all()` — добавить `RelatedThematic::block()`
- В `mainSection()` — добавить `RelatedThematic::block()` (сортировка по label сама поставит на место)
- В `topSection` / `bottomSection` / `tabs` / `modal` — **не добавлять**

### `app/Filament/Resources/Posts/Pages/CreatePost.php`

```php
private function appendDefaultMainBlock(array $state): array
{
    if (empty($state)) {
        return $state;
    }

    return [
        ...$state,
        ...RelatedThematic::getDefaultBlock(),
        ...CategoryList::getDefaultBlock(),
    ];
}
```

### `app/Filament/Resources/Projects/Pages/CreateProject.php`

Аналогично.

## Files

- **EDIT** `app/Blocks/BlockRenderRegistry.php`
- **EDIT** `app/Filament/Components/BlockRegistry/BlockRegistry.php`
- **EDIT** `app/Filament/Resources/Posts/Pages/CreatePost.php`
- **EDIT** `app/Filament/Resources/Projects/Pages/CreateProject.php`

## References

- `app/Filament/Resources/Posts/Pages/CreatePost.php:30-40` — текущий `appendDefaultMainBlock`
- `app/Filament/Resources/Projects/Pages/CreateProject.php:20-30` — то же для проектов
- `app/Filament/Components/BlockRegistry/BlockRegistry.php` — все методы

## Gotchas

- Порядок в `appendDefaultMainBlock`: **сначала пользовательский контент,
  потом RelatedThematic, потом CategoryList** — тематическая подборка
  выше «просто списка категорий» и не разрывает основной поток.
- `if (empty($state))` — сохраняем существующее поведение: если main-секция
  вообще пуста, дефолты не подмешиваем. Это важно, иначе Post/Project
  можно было бы создать только с двумя автоблоками — не всегда нужно.
- В `mainSection()` — `collect(...)->sortBy(fn ($b) => (string) $b->getLabel())`
  уже стоит; label «Тематическая подборка» встанет в правильную позицию
  по алфавиту.

## Checklist

- [ ] `BlockRenderRegistry::map()` возвращает наш renderer для ключа `related-thematic`
- [ ] `BlockRegistry::all()` содержит наш блок
- [ ] `BlockRegistry::mainSection()` содержит наш блок
- [ ] `BlockRegistry::topSection|bottomSection|tabs|modal` — **не содержат**
- [ ] `CreatePost` и `CreateProject` инжектят наш блок перед `CategoryList`
- [ ] Ручной тест: создать Post → в main-секции идут пользовательские блоки, потом RelatedThematic, потом CategoryList
- [ ] `sail bin pint --dirty --format agent`
