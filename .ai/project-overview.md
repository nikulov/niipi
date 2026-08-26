# NIiPI — обзор проекта

Корпоративный сайт-CMS на Laravel 12 + Filament 4. Управляет страницами,
новостями, проектами, меню, футерами, формами и их отправками.

## Тип проекта

CMS-сайт с публичной частью и админ-панелью Filament.

## Наследство: переезд с WordPress

`niipigrad.ru` до этого проекта работал на WordPress, и старые URL живы в
индексе поисковиков и в чужих ссылках. Редиректов со старых адресов на новые
**не заводили** — все они отдают 404.

Что прилетает в логи прода и не является багом кода:

- `/feed/`, `/comments/feed/`, `/cat/news/`, `/cat/news/page/NN/`,
  `/wp-json/*`, `/wp-content/*`, старые slug'и новостей без префикса;
- `/kontakt`, `/contact`, `/contacts.php`, `/about`, `/about.html` и прочие
  варианты статических страниц.

При разборе 404 с прода это первое, что нужно отсечь: осмысленных 404 там
около 3000 из 20 000 за двое суток, остальное — legacy-хвост и сканеры.
Подробный разбор с цифрами — [plans/bug-report-2026-08-04.md](plans/bug-report-2026-08-04.md),
пункты #27 и #28.

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
