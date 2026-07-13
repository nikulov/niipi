# Паттерн: HasBlockSections на модели

Как модели контента (Page/Post/Project) выставляют блоки для рендера.

## Интерфейс
`App\Blocks\Contracts\HasBlockSections`:

```php
public function getBlocksForSection(?string $section): array;   // null = все секции подряд
public function getRenderCacheId(): string;                     // для кэш-ключа
public function getRenderUpdatedAtTimestamp(): int;             // для кэш-ключа
```

## Три JSON-секции
Модели хранят блоки в трёх колонках, кастованных как `array`:

```php
protected $casts = [
    'top_section' => 'array',
    'main_section' => 'array',
    'bottom_section' => 'array',
];
```

`getBlocksForSection()` мапит имя секции → колонку. Пример — `app/Models/Post.php`.

## Кэш-идентификатор
- `getRenderCacheId(): 'post:' . $this->getKey()` — префикс = тип модели.
- `getRenderUpdatedAtTimestamp()` — `updated_at?->timestamp ?? 0`.

Эти два метода используются `ContentRenderer` (сейчас кэш закомментирован, но
готов к включению).

## Дефолтный набор блоков
Для новой записи — статический метод `getDefaultBlock(): array`, возвращает
структуру `[['type' => ..., 'data' => [...]], ...]`. Используется в Filament при
создании (см. `Post::getDefaultBlock()`).

## Trait HasSectionOptions
`App\Models\Concerns\HasSectionOptions` — общая механика для «блоков-настроек»,
не входящих в основную последовательность (например, `BgForMainSection`).
`ContentRenderer` пропускает их через `isSectionOptionBlock($blockType)`.