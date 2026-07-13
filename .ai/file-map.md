# Карта файлов

## Корень
- `composer.json`, `package.json` — зависимости
- `compose.yaml` — Sail (mysql, valkey, meilisearch, mailpit)
- `phpunit.xml` — конфиг тестов
- `deploy-stage.sh`, `deploy-prod.sh` — деплой
- `prettier.config.cjs`, `.prettierignore` — форматирование (blade + tailwind)
- `bootstrap/app.php` — сборка приложения
- `routes/web.php` — публичные роуты
- `routes/console.php` — консольные команды

## app/
| Путь | Что там |
|---|---|
| `Models/` | Eloquent модели (Page, Post, Project, Category, Menu, Footer, GlobalSetting, Form, FormField, FormSubmission, FormSubmissionFile, User) |
| `Models/Concerns/` | Общие трейты моделей |
| `Http/Controllers/` | `ContentController`, `MenuController` |
| `Livewire/Components/` | `ProjectsFull`, `NewsFull`, `AbstractContentFull` |
| `Livewire/Forms/` | `PublicForm` |
| `Filament/Resources/{Entity}/` | Filament ресурс — `Pages/`, `Schemas/`, `Tables/`, `RelationManagers/` |
| `Filament/Components/BlockRegistry/BlockRegistry.php` | Реестр блоков для админки |
| `Filament/Forms/Components/` | Кастомные поля Filament |
| `Filament/Support/`, `Filament/Widgets/` | Утилиты и виджеты |
| `Blocks/BlockRenderRegistry.php` | Реестр рендереров |
| `Blocks/Renderers/` | Рендер каждого типа блока (см. ниже) |
| `Blocks/Contracts/` | Интерфейсы блоков |
| `Services/` | `ContentRenderer`, `NewsQuery`, `ProjectsQuery`, `Forms/` |
| `Actions/Forms/` | Action-классы для форм |
| `Enums/` | Статусы и типы |
| `Providers/` | `AppServiceProvider`, `AuthServiceProvoider` (sic), `Filament/` |
| `Observers/`, `Policies/`, `Presenters/`, `Contracts/` | По назначению |
| `Mail/`, `Jobs/`, `View/` | Почта, очереди, view-биндинги |
| `helpers.php` | Глобальные хелперы (подключается в `bootstrap/app.php`) |

## Блоки
`app/Blocks/Renderers/`:
Accordion, AccordionLight, Button, CardsBlockWithButton,
CardsBlockWithImageTitle, CategoryList, Form, Gallery, ImageFull,
ImageText, ImageTittleFullWidth (sic), InfoBlockWithAchievements,
InfoBlockWithButtons, ModalBlock, NewsBlock, NewsFull, ProjectsBlock,
ProjectsFull, SliderFullWidth, TabsBlock, TextFull, Title, YandexMap.

## resources/views/
- `layout/` — макеты
- `components/` — Blade-компоненты: `buttons/`, `form/`, `icon/`, `layout/`,
  `logo/`, `menu/`, `other/`, `sections/`
- `livewire/components/`, `livewire/forms/` — шаблоны Livewire
- `includes/`, `emails/`, `forms/`, `vendor/`

## database/
- `migrations/` — миграции (нумерация от 2026-*)
- `factories/`, `seeders/`

## tests/
- `Unit/`, `Feature/` — по PHPUnit 11