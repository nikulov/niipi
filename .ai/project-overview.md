# NIiPI — обзор проекта

Корпоративный сайт-CMS на Laravel 12 + Filament 4. Управляет страницами,
новостями, проектами, меню, футерами, формами и их отправками.

## Тип проекта

CMS-сайт с публичной частью и админ-панелью Filament.

## Публичная часть

- Роуты в `routes/web.php` (см. [architecture.md](architecture.md#маршрутизация)).
- Контент рендерится из блочной модели через `App\Blocks\BlockRenderRegistry` и
  соответствующие рендереры в `app/Blocks/Renderers/`.
- Livewire-компоненты для интерактивных фрагментов (формы, «полные» списки
  проектов/новостей).

## Админ-панель (Filament 4)

Ресурсы под `app/Filament/Resources/`:

- Pages, Posts (news), Projects, Categories
- Menus, Footers, GlobalSettings
- Forms + FormSubmissions + вложения
- Users

Формы Filament v4 — со схемами (`Schemas/`), таблицами (`Tables/`),
кастомными компонентами (`app/Filament/Components/`, `app/Filament/Forms/Components/`)
и блоками из реестра (`app/Filament/Components/BlockRegistry/BlockRegistry.php`).

## Окружения

- `staging` — текущая ветка (см. `deploy-stage.sh`)
- `main` — прод (см. `deploy-prod.sh`)

Подробнее по стеку — [architecture.md](architecture.md).
