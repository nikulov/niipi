# Архитектурные решения

Хронологический журнал заметных решений. Каждая запись — краткая причина.

## Filament 4 как единственная админка
Все CRUD-операции — через Filament Resources под `app/Filament/Resources/`.
Кастомные потребности закрываются `Filament/Forms/Components/` и
`Filament/Components/`, не отдельными контроллерами.

## Блочный контент через реестр
Тела Page/Post/Project — массив блоков. Каждый блок:
- рендерится классом из `app/Blocks/Renderers/`
- регистрируется в `App\Blocks\BlockRenderRegistry`
- имеет админ-конфиг в `app/Filament/Components/BlockRegistry/BlockRegistry.php`

Причина: единое место расширения — добавление блока не трогает контроллеры/шаблоны страниц.

## Публичные формы на Livewire
`App\Livewire\Forms\PublicForm` рендерит форму по конфигу модели `Form` и
её `FormField`. Action-логика вынесена в `App\Actions\Forms\*`.

## Единый ContentController + catch-all роут
Все публичные страницы (кроме `/news/*`, `/projects/*`) идут через один
контроллер и slug-роут `/{slug}` с exclude-паттерном для `admin|api|login|register`.

## Cookie `cookie_consent` вне encryptCookies
См. `bootstrap/app.php`. Причина: доступ к состоянию согласия из frontend без расшифровки.

## Инфраструктура — Sail
Все команды выполнять через `vendor/bin/sail`. Смотри [workflow.md](workflow.md).

## Двухслойная авторизация
Видимость ресурса — trait `RoleAccessResource` (grubby-фильтр по роли), права
на действия — Policies. `BasePolicy::before()` даёт Admin bypass. Причина:
Editor/Viewer видит ресурс, но фактические права check-ает уже политика.

## Формы: rate-limit по IP
`SubmitFormAction` использует `RateLimiter` (5 попыток за 300 секунд по
ключу `forms:{form_id}:{ip}`), плюс honeypot `website`. Причина — простая
дефолтная защита без внешних сервисов.

## Single-row настройки: Footer, GlobalSetting, Menu
Хранятся в таблицах c одним запросом на всё приложение. `Footer` и
`GlobalSetting` кэшируются `rememberForever` со сбросом на `saved`/`deleted`.
`Menu` — `first()` каждый раз (нужен нормализатор href'ов).

## Дефолтная страница — slug `home`
`ContentController::normalizePageSlug()` — `null`, `/`, `home` → `home`.
Модель `Page` со `slug='home'` — стартовая страница. Slug'ы всегда без
ведущего `/` (см. `Page::setSlugAttribute`).