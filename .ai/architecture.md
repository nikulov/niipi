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
  Cookie `cookie_consent` исключён из encryptCookies. Health-check: `/up`.
- `bootstrap/providers.php` — три провайдера: `AppServiceProvider`,
  `AuthServiceProvoider` (sic), `Filament\AdminPanelProvider`.
- `routes/web.php`:
  - `/` → `ContentController@page`
  - `/news/{slug}` → `ContentController@post`
  - `/projects/{slug}` → `ContentController@project`
  - `/sitemap.xml` → `SitemapController` (обязательно **выше** catch-all `/{slug}`,
    иначе regex страницы заберёт запрос)
  - `/{slug}` (кроме `admin|api|login|register`) → `ContentController@page`
  - `/login` → редирект на `/admin/login`
- Filament регистрируется через `App\Providers\Filament\AdminPanelProvider`
  (панель `admin`, path `/admin`). Автопоиск `Resources`/`Pages`/`Widgets`.
  `navigationGroups` — жёстко зашитые русские строки
  (`Публикации`, `Страницы`, `Формы`, `Настройки`).

## Слои и папки (app/)
- `Models/` — Eloquent-модели. Concerns в `Models/Concerns/`.
- `Http/Controllers/` — тонкие контроллеры. Живые: `ContentController`.
  `MenuController` — пустой класс, не имеет роутов (кандидат на удаление).
- `Livewire/` — компоненты (`Components/`, `Forms/`).
- `Filament/` — админка (`Resources/`, `Forms/Components/`, `Components/` —
  Block-компоненты, `Support/RoleAccessResource.php`, `Widgets/SubmissionsStats.php`).
- `Blocks/` — рендер блочного контента:
  - `BlockRenderRegistry.php` — реестр
  - `Renderers/` — по одному классу на тип блока
  - `Contracts/` — интерфейсы (`BlockRenderer`, `HasBlockSections`)
- `Services/` — доменные сервисы (`ContentRenderer`, `NewsQuery`,
  `ProjectsQuery`, `Forms/*`).
- `Actions/Forms/SubmitFormAction.php` — оркестратор отправки формы (rate-limit,
  валидация, сохранение, диспатч job писем).
- `Presenters/Blocks/` (карточки News/Projects) и `Presenters/Forms/`
  (public form + submission) — тонкие DTO/адаптеры для view.
- `Enums/` — статусы и типы (см. [domain.md](domain.md)).
- `Providers/` — сервис-провайдеры, включая Filament. `AuthServiceProvoider`
  (sic) — с опечаткой в имени, `$policies` массив не используется
  (Laravel 12 подхватывает политики по конвенции).
- `Observers/` — `PostObserver`, `PageObserver`, `ProjectObserver`: авто-
  выставляют `published_at = now()` при первой публикации.
- `Policies/` — все наследуют `BasePolicy` (Admin bypass через `before()`).
- `View/Composers/FooterComposer.php` — шарит `$footer` в `includes.footer`.
- `Contracts/HasMeta.php` — SEO-мета в контроллере.
- `Mail/`, `Jobs/SendFormSubmissionEmails.php` — асинхронная отправка писем.

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