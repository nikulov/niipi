# Карта файлов

## Корень
- `composer.json`, `package.json` — зависимости
- `compose.yaml` — Sail (mysql, valkey, meilisearch, mailpit)
- `phpunit.xml` — конфиг тестов
- `deploy-stage.sh`, `deploy-prod.sh` — деплой (staging/prod)
- `prettier.config.cjs`, `.prettierignore` — форматирование (blade + tailwind)
- `bootstrap/app.php` — сборка приложения
- `bootstrap/providers.php` — список провайдеров (внимание к опечатке `AuthServiceProvoider`)
- `routes/web.php` — публичные роуты
- `routes/console.php` — консольные команды (только `inspire`)
- `vite.config.js` — Vite + `@tailwindcss/vite`

## app/
| Путь | Что там |
|---|---|
| `Models/` | Eloquent модели (Page, Post, Project, Category, Menu, Footer, GlobalSetting, Form, FormField, FormSubmission, FormSubmissionFile, User) |
| `Models/Concerns/HasSectionOptions.php` | Механика «блоков-настроек» (например, `bg-for-main-section`) |
| `Http/Controllers/ContentController.php` | Публичные страницы: `page()`, `post()`, `project()` |
| `Http/Controllers/SitemapController.php` | `__invoke` → XML-карта из Page/Post/Project, кэш тегом `sitemap` (TTL 1ч, backstop) |
| `Http/Controllers/MenuController.php` | **Пустой класс**, роутов не имеет, кандидат на удаление |
| `Livewire/Components/` | `AbstractContentFull` (база), `ProjectsFull`, `NewsFull` — списки с категориями и пагинацией |
| `Livewire/Forms/PublicForm.php` | Публичная форма (WithFileUploads + honeypot) |
| `Filament/Resources/{Entity}/` | Filament ресурс — `Pages/`, `Schemas/`, `Tables/`, при необходимости `RelationManagers/` |
| `Filament/Resources/Forms/RelationManagers/FieldsRelationManager.php` | Поля формы (relation manager) |
| `Filament/Components/*.php` | Filament Block-компоненты (по одному на тип блока) |
| `Filament/Components/BgForMainSection.php` | Спец-блок для «настроек секции» |
| `Filament/Components/BlockRegistry/BlockRegistry.php` | Реестр блоков для Filament (`all()`, `topSection()`, `mainSection()`, `bottomSection()`, `tabs()`, `modal()`) |
| `Filament/Forms/Components/CustomRepeater.php` | Repeater с нумерованными label'ами |
| `Filament/Forms/Components/UrlInput.php` | TextInput с префиксом `niipigrad.ru/` и кнопкой открытия |
| `Filament/Support/RoleAccessResource.php` | Trait: `shouldRegisterNavigation()` + `canViewAny()` по `allowedRoles()` |
| `Filament/Widgets/SubmissionsStats.php` | Dashboard stat-виджет по заявкам |
| `Blocks/BlockRenderRegistry.php` | Реестр рендереров (map `type => Renderer`) |
| `Blocks/Renderers/` | Рендер каждого типа блока (см. ниже) |
| `Blocks/Contracts/BlockRenderer.php`, `HasBlockSections.php` | Интерфейсы блочного рендера |
| `Services/ContentRenderer.php` | Диспатч блоков секции; кэш пока закомментирован |
| `Services/NewsQuery.php`, `ProjectsQuery.php` | Публичные выборки (list + paginate) |
| `Services/Forms/` | `FormRulesBuilder`, `FormValidationAttributesBuilder`, `SubmissionCreator`, `SubmissionDataNormalizer`, `SubmissionFilesStorer`, `FormEmailTemplateRenderer` |
| `Actions/Forms/SubmitFormAction.php` | Оркестратор: rate-limit → валидация → нормализация → сохранение → job писем |
| `Presenters/Forms/PublicFormPresenter.php` | `viewData` для `PublicForm` |
| `Presenters/Forms/FormSubmissionPresenter.php` | Для админки/писем |
| `Presenters/Blocks/` | `NewsBlockPresenter`, `NewsFullPresenter`, `ProjectsBlockPresenter`, `ProjectsFullPresenter` — DTO карточек |
| `Enums/` | `PageStatus`, `PostStatus`, `ProjectStatus`, `CategoryStatus`, `CategoryType`, `FormApplicantType`, `FormSubmissionStatus`, `UserRole` |
| `Providers/AppServiceProvider.php` | Observers, `helpers.php`, `Filament\Notifications::alignment`, share `year`, `settings`, footer composer |
| `Providers/AuthServiceProvoider.php` | **Имя с опечаткой** (класс и файл). `protected $policies = [...]` — мёртвый код (Laravel 12 автоподхватывает политики по конвенции) |
| `Providers/Filament/AdminPanelProvider.php` | Панель `admin`, тема, автопоиск ресурсов/страниц/виджетов, hardcoded `navigationGroups` (русские строки) |
| `Observers/` | `PageObserver`, `PostObserver`, `ProjectObserver` — авто-`published_at` при переходе в Published |
| `Policies/BasePolicy.php` | `before()` = Admin bypass; хелперы `isEditor`, `isViewer`, `isEditorOrViewer` |
| `Policies/*Policy.php` | По одной на модель, наследуются от `BasePolicy` |
| `Contracts/HasMeta.php` | `meta(): array` — для страниц с SEO-мета |
| `View/Composers/FooterComposer.php` | `includes.footer` → `$footer` из `Footer::cachedData()` |
| `View/Components/Menu/` | Blade-компоненты `DesktopFooter`, `Top` |
| `Mail/` | `AdminFormSubmissionMail`, `UserFormSubmissionMail`, `TemplatedFormSubmissionMail` |
| `Jobs/SendFormSubmissionEmails.php` | Асинхронная отправка писем (tries=5, backoff [60,300,900]) |
| `helpers.php` | `public_asset()` — Storage disk `public` или прямой URL |

## Блоки
`app/Blocks/Renderers/`:
Accordion, AccordionLight, Button, CardsBlockWithButton,
CardsBlockWithImageTitle, CategoryList, Form, Gallery, ImageFull,
ImageText, ImageTittleFullWidth (sic — с двойной `t`), InfoBlockWithAchievements,
InfoBlockWithButtons, ModalBlock, NewsBlock, NewsFull, ProjectsBlock,
ProjectsFull, SliderFullWidth, TabsBlock, TextFull, Title, YandexMap.

Filament-компоненты в `app/Filament/Components/` — один-в-один с рендерерами
плюс `BgForMainSection` (спец-блок настройки).

## resources/views/
- `layout/` — макеты (в т.ч. `layout.page`)
- `components/` — Blade-компоненты: `buttons/`, `form/`, `icon/`, `layout/`,
  `logo/`, `menu/`, `other/`, `sections/`
- `livewire/components/`, `livewire/forms/` — шаблоны Livewire
- `includes/`, `emails/`, `forms/`, `vendor/`
- `sitemap.blade.php` — XML для `/sitemap.xml` (см. `SitemapController`)

## resources/css/
- `app.css` — Tailwind 4 корневой файл (`@theme`, `@source`, `@utility`)
- `filament/` — темы Filament

## database/
- `migrations/` — миграции. Есть базовые `0001_01_01_*` + 30+ доменных
  (`2025_10_*` → `2026_02_*`).
- `factories/UserFactory.php`
- `seeders/DatabaseSeeder.php`, `FooterSeeder.php`, `UserSeeder.php`

## tests/
- `Unit/` — `Actions/`, `Blocks/Renderers/`, `Jobs/`, `Mail/`, `Models/`,
  `Observers/`, `Presenters/Blocks|Forms`, `Providers/`, `Services/`, `Services/Forms/`
- `Feature/` — `Http/ContentControllerTest.php`, `Integration/FormSubmissionFlowTest.php`,
  `Livewire/{NewsFull,ProjectsFull,PublicForm}Test.php`

## lang/
- `panel.php` в каждой локали — все админ-строки (`__('panel.*')`).
