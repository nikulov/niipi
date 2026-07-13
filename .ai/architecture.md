# Архитектура

## Стек
- **PHP** 8.4
- **Laravel** 12
- **Filament** 4 (админка)
- **Livewire** 3.7 (интерактивные фрагменты)
- **Frontend**: Tailwind 4 (`@tailwindcss/vite`), Alpine.js 3 + плагины,
  Swiper 12, Vite 7
- **Инфраструктура (Sail)**: MySQL, Valkey (Redis-совместимый), Meilisearch,
  Mailpit (см. `compose.yaml`)

## Точки входа
- `bootstrap/app.php` — сборка приложения. Регистрирует `app/helpers.php`.
  Cookie `cookie_consent` исключён из encryptCookies.
- `routes/web.php`:
  - `/` → `ContentController@page`
  - `/news/{slug}` → `ContentController@post`
  - `/projects/{slug}` → `ContentController@project`
  - `/{slug}` (кроме `admin|api|login|register`) → `ContentController@page`
  - `/login` → редирект на `/admin/login`
- Filament регистрируется через провайдер в `app/Providers/Filament/`.

## Слои и папки (app/)
- `Models/` — Eloquent-модели. Concerns в `Models/Concerns/`.
- `Http/Controllers/` — тонкие контроллеры (`ContentController`, `MenuController`).
- `Livewire/` — компоненты (`Components/`, `Forms/`).
- `Filament/` — админка (Resources, Forms, Components, Support, Widgets).
- `Blocks/` — рендер блочного контента:
  - `BlockRenderRegistry.php` — реестр
  - `Renderers/` — по одному классу на тип блока
  - `Contracts/` — интерфейсы
- `Services/` — доменные сервисы (`ContentRenderer`, `NewsQuery`,
  `ProjectsQuery`, `Forms/`).
- `Actions/Forms/` — Action-классы для форм.
- `Enums/` — статусы и типы (см. [domain.md](domain.md)).
- `Providers/` — сервис-провайдеры, включая Filament.
- `Observers/`, `Policies/`, `Presenters/`, `Mail/`, `Jobs/`, `View/`, `Contracts/`.

## Рендер блочного контента
Модели Page/Post/Project хранят набор блоков. `Services\ContentRenderer` +
`Blocks\BlockRenderRegistry` матчат тип блока на соответствующий класс из
`Blocks/Renderers/` (Accordion, Cards, Gallery, Form, YandexMap, Tabs,
NewsBlock, ProjectsBlock и т.д.). В админке эти же блоки конфигурируются
через `app/Filament/Components/BlockRegistry/BlockRegistry.php`.

## Frontend
- Точка сборки — Vite. Скрипты: `npm run dev` / `npm run build`.
- Тема оформления: Tailwind 4. Форматтер: Prettier с plugin-blade +
  plugin-tailwindcss.

## Тесты
- PHPUnit 11, конфиг `phpunit.xml`. Тестовый env: `APP_ENV=testing`,
  `DB_DATABASE=testing`, sync-очередь, array-cache/session/mail.
- Наборы: `tests/Unit`, `tests/Feature`.