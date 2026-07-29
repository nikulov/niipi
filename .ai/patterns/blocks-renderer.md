# Паттерн: BlockRenderer

Единая форма для всех рендереров блочного контента.

## Интерфейс

`App\Blocks\Contracts\BlockRenderer`:

```php
public static function key(): string;      // ключ типа в JSON-структуре блока
public static function version(): string;  // бампать при изменении логики (для кэша)
public function render(array $data, HasBlockSections $model, int $index): string;
```

## Канонический пример

`app/Blocks/Renderers/TitleRenderer.php` — тонкий класс: возвращает
`view('components.sections.title', $data)->render()`.

## Правила

- **`key()` эксклюзивен** — не совпадать с другими блоками; тот же ключ должен
  использовать соответствующий Filament-компонент в
  `app/Filament/Components/*.php`.
- **Никакой бизнес-логики в render**. Тяжёлое (выборки данных) — через сервисы
  (`NewsQuery`, `ProjectsQuery`) или Livewire-компоненты (`NewsFull`, `ProjectsFull`).
- **`$data`** приходит из JSON-колонки модели (`main_section` и т.п.); значения
  считать как ненадёжный ввод из админки — проверять наличие ключей.
- **`$index`** — позиция блока в секции; полезно для id/якорей.
- **`$model`** — источник для сквозных данных (например, кэш-ключ через
  `getRenderCacheId()`).

## Диспетчеризация

`App\Blocks\BlockRenderRegistry::for(string $type)` возвращает класс или `null`.
Регистрируется в методе `map()` — там же список ключей.

`App\Services\ContentRenderer` перебирает `getBlocksForSection()`, диспетчит
через registry, логирует неизвестные типы через `Log::warning('Unknown block type', ...)`.
