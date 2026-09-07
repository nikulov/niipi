# Filament 4 — заметки

Версия: `filament/filament: ^4.0`.

## Раскладка ресурса

В v4 ресурс разбит на подпапки:

```
app/Filament/Resources/{Models}/
  {Model}Resource.php
  Pages/          Create/Edit/List/View
  Schemas/        {Model}Form.php, {Model}Infolist.php
  Tables/         {Models}Table.php
  RelationManagers/
```

## Схемы вместо Form

- Форма — статик-класс `{Model}Form::configure(Schema $schema): Schema`.
- Возвращаемый тип — **`Filament\Schemas\Schema`**, а не старый `Form`.
- Infolist — аналогично, `Filament\Schemas\Schema`.

## Иконки в навигации

`Heroicon` — backed enum, не строка:

```php
use BackedEnum;
use Filament\Support\Icons\Heroicon;

protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;
```

Строки `'heroicon-o-document-text'` тоже принимаются, но enum предпочтительнее —
подсказки IDE, никаких опечаток.

## Enum'ы статусов

Enum'ы, реализующие `Filament\Support\Contracts\HasColor` и `HasLabel`,
автоматически подхватываются в форме и таблице (цвет badge, подпись option).
См. `patterns/enum-with-color-label.md`.

## Блочный контент через Builder

Кастомные блоки — статические классы под `app/Filament/Components/*.php`,
возвращают `Filament\Forms\Components\Builder\Block`. Регистрируются в
`app/Filament/Components/BlockRegistry/BlockRegistry.php`. См.
[../patterns/filament-block.md](../patterns/filament-block.md).

## Апгрейд после composer install

`composer.json` содержит скрипт:

```json
"post-autoload-dump": ["@php artisan filament:upgrade"]
```

При обновлении Filament версии — этот скрипт бежит автоматически. Если что-то
сломалось после `composer install` — проверить его вывод.

## Локализация

Строки — `lang/*/panel.php`. Ключи используются повсюду:
`__('panel.new')`, `__('panel.published')`, `__('panel.heading_size')` и т.д.

## Роли и доступ

Trait `App\Filament\Support\RoleAccessResource` с методом
`allowedRoles(): array` на `UserRole[]`. См.
[../patterns/role-access-resource.md](../patterns/role-access-resource.md).

## `copyable()` + `limit()` в колонке — копируется обрезанное

`TextColumn` кладёт в буфер отформатированное состояние, а `formatState()`
применяет `limit()`:

```php
// vendor/filament/tables/src/Columns/TextColumn.php:292
$copyableStateJs = Js::from($this->getCopyableState($stateItem) ?? $this->formatState($stateItem));
```

Так что `->copyable()->limit(10)` копирует `personal_p...` вместе с
многоточием. Лечится явным `->copyableState(fn (string $state): string =>
$state)` — заданный `copyableState` отменяет обращение к `formatState()`
целиком. Актуально для любой колонки, где копирование сочетается с `limit()`,
`words()` или `formatStateUsing()` (пример — колонка `name` в
`FieldsRelationManager`).
