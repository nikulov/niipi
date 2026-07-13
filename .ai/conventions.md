# Соглашения

## Стиль кода PHP
- **Форматтер:** Laravel Pint. Запуск: `vendor/bin/sail bin pint --dirty --format agent` после правки PHP.
- **PSR-4:** `App\` → `app/`, `Database\Factories\` → `database/factories/`,
  `Database\Seeders\` → `database/seeders/`, `Tests\` → `tests/`.

## Frontend
- **Форматтер:** Prettier + `prettier-plugin-blade` + `prettier-plugin-tailwindcss`.
  Запуск: `npm run format` (или через Sail).
- **Tailwind 4** — utility-first. Классы упорядочивает prettier-plugin-tailwindcss.
- Alpine.js — маленькие интерактивы; крупные — Livewire-компоненты.

## Именование
- **Модели:** ед. число, PascalCase (`Post`, `Project`).
- **Enum:** `{Сущность}{Свойство}` (`PostStatus`, `CategoryType`).
- **Filament Resources:** `{Модель}Resource` в
  `app/Filament/Resources/{Модели}/` со стандартными подпапками
  (`Pages/`, `Schemas/`, `Tables/`, при нужде `RelationManagers/`).
- **Блоки:** `{Название}Renderer` в `app/Blocks/Renderers/`.
- **Livewire:** `App\Livewire\Components\{Имя}` для UI-фрагментов,
  `App\Livewire\Forms\{Имя}` для форм.
- **Services:** осмысленное существительное/глагол-существительное
  (`ContentRenderer`, `NewsQuery`).

## Блочный контент
- Новый тип блока = класс в `app/Blocks/Renderers/` + регистрация в
  `App\Blocks\BlockRenderRegistry` + запись в
  `app/Filament/Components/BlockRegistry/BlockRegistry.php` для админки.
- Шаблоны — Blade в `resources/views/components/sections/` или соответствующем
  подкаталоге.

## Формы
- Публичные — Livewire-компонент `PublicForm` под управлением Filament-конфига `Form`.
- Логика отправки — `App\Actions\Forms\*`.
- Файлы вложений — модель `FormSubmissionFile`.

## Миграции
- Именование Laravel-стандарт: `YYYY_MM_DD_HHMMSS_action_on_table.php`.
- Изменения существующих таблиц оформлять отдельными миграциями
  (`add_x_to_y_table`, `change_x_type_on_y_table`, `drop_x_from_y_table`).

## Тесты
- PHPUnit 11 (не Pest, судя по `phpunit.xml`).
- `tests/Unit/` — юниты; `tests/Feature/` — фичи (в т.ч. HTTP).
- Тестовая БД: `DB_DATABASE=testing` (см. `phpunit.xml`).

## Прочее
- Cookie `cookie_consent` не шифруется (см. `bootstrap/app.php`).
- Хелперы приложения — `app/helpers.php`, подключается в `bootstrap/app.php`.
- **Никогда не удалять существующие тесты без явного разрешения.**