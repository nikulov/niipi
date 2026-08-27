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

## Диски: `throw => false`, но `report => true`

Сбой записи по-прежнему возвращает `false` вместо исключения — приложение
защищается само (проверки в `PublicForm` и `SubmissionFilesStorer`), а не
рассчитывает на падение. Но `'report' => true` включён обоим дискам: без него
неудачная запись не оставляет следа вообще нигде, и сломанный `livewire-tmp`
на проде выяснялся только по жалобе посетителя (баг #23). `'throw' => true`
диску `local` рассматривали и отложили: цена — гонка в vendor'ном
`cleanupOldUploads()`, которая даст редкие 500 на загрузке. Матрица поведения —
[manual/filesystem-disks.md](manual/filesystem-disks.md).

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

## Системные письма брендируем темой markdown, а не подменой нотификаций

`resources/views/vendor/mail/html/layout.blade.php` включает
`emails.email-template` — и все `MailMessage` (Laravel и Filament) едут в
брендовой обёртке разом, включая будущие. Альтернатива — биндить свой класс
нотификации и рисовать `->view('emails.email-template')` — отвергнута: обёртка
под каждое новое системное письмо. Следствие: `html/themes/default.css`
инлайнится по всему документу, поэтому его селекторы заскоплены на `.letter`.

## Хвост записи — `btn-secondary`, данные прода выровнены 2026-08-27

`ShareButton::getDefaultBlock()` отдаёт `btn-secondary`, а не `btn-primary`.
Причина — исторические данные: все кнопки «ВСЕ НОВОСТИ» на проде были
`btn-secondary`, и код с `btn-primary` заставлял бы каждую новую новость
выглядеть иначе, чем остальные.

Тогда же разовыми скриптами приведены к общему виду `main_section` всех
новостей и проектов на проде. Было — блок `button` («ВСЕ НОВОСТИ»/«ВСЕ
ПРОЕКТЫ»), у части записей вообще ничего после `category-list`:

| Модель    | Записей | Стало                                                 |
| --------- | ------- | ----------------------------------------------------- |
| `Post`    | 26 из 27 | `category-list` → `share-button` → `related-thematic` |
| `Project` | 36 из 37 | `category-list` → `share-button`                      |

Не тронуты: `Project` 1018 — `main_section` пуст целиком (черновик), цепляться
не к чему; `Post` 1034 уже был эталонным.

Бэкапы до правки лежат в `shared/storage/app/backups/` —
`posts-20260827-123304.json` и `projects-20260827-125010.json` (полный JSON
всех записей с сырыми значениями трёх секций).

Два момента, которые всплыли по дороге и стоят внимания при следующей такой
правке:

- `bg-for-main-section` может стоять **после** хвоста (проекты 1011, 1043).
  Это блок-настройка, он не рендерится, поэтому «последний блок» — плохой
  признак конца секции; ориентироваться надо на `category-list` перед кнопкой.
- В данных подпись капсом («ВСЕ НОВОСТИ», «ВСЕ ПРОЕКТЫ»), а `panel.all_news` /
  `panel.all-projects` отдают «Все новости» / «Все проекты». Записи, созданные
  через админку, получают подпись в другом регистре, чем исторические.

Кэш после такой правки чистить руками не нужно: `booted()` у обеих моделей
флашит свои теги на `saved`.
