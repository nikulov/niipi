# Паттерн: Enum со статусом для Filament

Enum'ы статусов реализуют интерфейсы Filament — тогда админка сама подхватывает
цвет, подпись и иконку.

## Пример

`app/Enums/PostStatus.php`:

```php
enum PostStatus: string implements HasColor, HasLabel
{
    case Draft     = 'draft';
    case Published = 'published';
    case Archived  = 'archived';

    public function getColor(): ?string  { /* match */ }
    public function getLabel(): ?string  { /* match с __('panel.*') */ }
    public function getIcon(): ?string   { /* match, heroicon-o-* */ }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
```

## Подключение

1. **Модель**: `protected $casts = ['status' => PostStatus::class];`.
2. **Filament форма/таблица**: указывать enum напрямую — Filament подхватит
   `HasColor`, `HasLabel` (импортируется из `Filament\Support\Contracts\*`).
3. **Иконка**: `heroicon-o-*` — стандартные heroicon имена.

## Опциональный метод `options()`

Возвращает `['value' => 'Label']` для мест, где нужен обычный ассоциативный
массив (например, невфреймворковый Select).

## Список enum'ов проекта

См. [../domain.md](../domain.md#enums-appenums).
