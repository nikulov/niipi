# Добавить новый тип блока

Когда: нужен новый вариант содержимого страницы (Page/Post/Project).

## Что создать

1. **Renderer** — `app/Blocks/Renderers/{Name}Renderer.php`, реализует
   `App\Blocks\Contracts\BlockRenderer`.
2. **Filament Block** — `app/Filament/Components/{Name}.php`, статический класс
   с методами `key()` и `block(): Filament\Forms\Components\Builder\Block`.
3. **Blade-шаблон** — обычно `resources/views/components/sections/{name}.blade.php`.

## Пример renderer

Опираться на `app/Blocks/Renderers/TitleRenderer.php`:

```php
final class TitleRenderer implements BlockRenderer
{
    public static function key(): string { return 'title'; }
    public static function version(): string { return '1'; }

    public function render(array $data, HasBlockSections $model, int $index): string
    {
        return view('components.sections.title', $data)->render();
    }
}
```

Ключ `key()` **должен совпадать** с ключом соответствующего Filament-блока —
именно он попадает в JSON колонок `top_section` / `main_section` / `bottom_section`.

## Регистрация

1. Добавить импорт и запись в `App\Blocks\BlockRenderRegistry::map()`
   (`app/Blocks/BlockRenderRegistry.php`).
2. Добавить блок в `app/Filament/Components/BlockRegistry/BlockRegistry.php`:
    - в `all()` — всегда
    - в `topSection()` / `mainSection()` / `bottomSection()` — если ограничен
      конкретной секцией

## Проверка

- В админке новый блок должен появиться в Builder-е нужной секции.
- На публичной странице блок рендерится через `ContentRenderer::renderSection()`
  (см. `app/Services/ContentRenderer.php`).
- Bump `version()` при заметных изменениях логики — используется в кэш-ключах.

## Форматирование

- `vendor/bin/sail bin pint --dirty`
- `vendor/bin/sail npm run format` для blade
