# Добавить Filament ресурс (v4)

Когда: появилась новая сущность, требующая CRUD в админке.

## Генерация
```bash
vendor/bin/sail artisan make:filament-resource {Model} --no-interaction
```

Filament 4 создаёт ресурс с раскладкой:
```
app/Filament/Resources/{Models}/
  {Model}Resource.php
  Pages/          — Create/Edit/List/View
  Schemas/        — {Model}Form.php, {Model}Infolist.php
  Tables/         — {Models}Table.php
  RelationManagers/  — по необходимости
```

## Обязательные штрихи для этого проекта
Опираться на `app/Filament/Resources/Posts/PostResource.php`:

- `use App\Filament\Support\RoleAccessResource;` и реализовать
  `protected static function allowedRoles(): array` со списком `UserRole`.
- `protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;`
  — иконка через enum `Filament\Support\Icons\Heroicon`.
- `getModelLabel()` / `getPluralModelLabel()` — локализованные строки через `__('panel.*')`.

## Форма и таблица
- Форма — статический метод `PostForm::configure(Schema $schema)`, возвращает
  `Filament\Schemas\Schema` (v4 использует `Schemas`, а не старый `Form`).
- Таблица — статический метод в `Tables/PostsTable.php`, возвращает
  `Filament\Tables\Table`.
- Infolist — `Schemas/PostInfolist.php`.

## Модель со статусом
Если модель имеет enum-статус:
- В `$casts` — `'status' => PostStatus::class`.
- Enum реализует `HasColor` и `HasLabel` из `Filament\Support\Contracts\*` —
  Filament подхватит цвет и подпись автоматически. Пример: `app/Enums/PostStatus.php`.

## Кэш
Если модель попадает в публичный кэш (Post/Project/Category и т.д.), см. паттерн
`patterns/cache-flush-on-save.md` и продумать теги.

## Локализация
Строки — в `lang/*/panel.php`. Ключи короткие: `panel.new`, `panel.published`.

## Пост-шаги
- Миграция + `sail artisan migrate`
- Тесты `tests/Feature/` для сложной логики
- `sail bin pint --dirty --format agent`