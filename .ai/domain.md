# Доменная модель

## Сущности (`app/Models/`)

- **Page** — статические/динамические страницы (`/{slug}`, `/`). Реализует
  `HasBlockSections`, `HasMeta`, использует `HasSectionOptions`.
- **Post** — новости (`/news/{slug}`). Пивот `category_post`. Флашит теги кэша
  `['news','categories']`.
- **Project** — проекты (`/projects/{slug}`). Пивот `category_project`.
  Флашит теги `['projects','categories']`.
  `sort_order` (nullable unsigned int) — ручной порядок в публичных списках:
  `1, 2, 3…` сверху, `0`/`null` — в конец, внутри одного значения — по
  `published_at` desc. Реализация — `scopeOrdered()`, применяется в
  `ProjectsQuery::list()` (значит, во всех публичных выборках). Колонка
  называется `sort_order`, а не `order` — `order` зарезервирован в MySQL.
- **Category** — категории. `type` (`CategoryType`: `posts`/`projects`) отделяет
  категории новостей от категорий проектов. Есть scope'ы `posts()`, `projects()`.
  Флашит теги обеих групп при save/delete.
- **Menu** — **single-row** таблица `menus`. Хранит `top_items`/`footer_items`
  как JSON-деревья. Публичный API — `getTopMenuItems()`, `getFooterMenuItems()`.
- **Footer** — **single-row** таблица `footer` (без `s`). Кэшируется
  `Cache::rememberForever('footer.data', ...)`, сбрасывается на save/delete.
- **GlobalSetting** — **single-row** таблица. Кэш `'global_settings'` навсегда,
  сбрасывается на save/delete. Достаётся через `GlobalSetting::getSetting()`.
- **Form** — конфиг формы: `title`, `applicant_type`, `is_active`, `settings`
  (json), почтовые поля (`recipient_admin_email`, `send_admin_mail`, `admin_mail_subject`,
  `admin_mail_body_md`, `send_user_mail`, `user_mail_subject`, `user_mail_body_md`,
  `user_mail_attachments`), `success_message`.
- **FormField** — поле формы:
    - `type`, `name`, `label` (longtext), `placeholder`
    - `required` (bool), `is_enabled` (bool), `sort`
    - `options` (json — `[{value, label, default?, disabled?}, ...]`; пустой `value` допустим **только** у `select` вместе с `disabled: true` — паттерн плейсхолдера с сохранением HTML5 `required`, у `radio` такие строки отбрасываются. `default` учитывается только у **первой** помеченной опции — `PublicFormPresenter::normalizeOptions()` гасит флаг у остальных, чтобы разметка (`@selected`/`@checked`) совпадала со state Livewire)
    - `rules` (json — assoc `правило => сообщение` или список)
    - `extra` (json — для `type=file`: `multiple`, `max_files`, `max_size_kb`, `accept_mimes`)
- **FormSubmission** — заявка. `data` (json), `status` (`FormSubmissionStatus`),
  `ip`, `user_agent`, `error_message`. Отношения `form()`, `files()`.
- **FormSubmissionFile** — вложение к заявке (`form_submission_id`).
- **User** — админ. `role` (`UserRole`). Реализует `FilamentUser`,
  `MustVerifyEmail`. `canAccessPanel(Panel): true` — доступ фильтруется через
  `RoleAccessResource` + политики.

## Enums (`app/Enums/`)

| Enum                   | Кейсы                                                                    | Применяется         |
| ---------------------- | ------------------------------------------------------------------------ | ------------------- |
| `PageStatus`           | Draft/Published/Archived                                                 | Page                |
| `PostStatus`           | Draft/Published/Archived                                                 | Post                |
| `ProjectStatus`        | Draft/Published/Archived                                                 | Project             |
| `CategoryStatus`       | Draft/Published(=`active`)/Archived — **value `Published` = `'active'` намеренно: категория «активна», а не «опубликована». Не выравнивать с `'published'`** | Category            |
| `CategoryType`         | Posts/Projects                                                           | Category (тематика) |
| `FormApplicantType`    | Person/Company/All                                                       | Form                |
| `FormSubmissionStatus` | New/Processing/Sent/Failed                                               | FormSubmission      |
| `UserRole`             | Admin/Editor/Viewer                                                      | User                |

Все реализуют `HasLabel` + `HasColor` (кроме `CategoryType` — `HasLabel + HasIcon`).
См. [patterns/enum-with-color-label.md](patterns/enum-with-color-label.md).

## Блочный контент

Page/Post/Project содержат три JSON-секции: `top_section`, `main_section`,
`bottom_section` (cast `array`). Каждый блок — `['type' => 'ключ', 'data' => [...]]`.
Ключ типа соответствует классу в `app/Blocks/Renderers/` — список типов см. в
[file-map.md](file-map.md#блоки).

Специальный блок `bg-for-main-section` — не рендерится, но `HasSectionOptions`
достаёт его данные через `getBgForMainSection()`.

При создании Post и Project в main-секцию (если она не пуста) автоматически
добавляются три блока в порядке: `related-thematic` (тематическая подборка
из категорий записи) → `category-list` (список категорий записи) →
`share-button` («Поделиться + кнопка», ведёт на `/news` или `/projects`
с подписью «Все новости»/«Все проекты»). Логика — в
`CreatePost::appendDefaultMainBlock()` и
`CreateProject::appendDefaultMainBlock()`, данные кнопки собирает
`ShareButton::getDefaultBlock($btnUrl, $btnLabel)` (`btn-primary`,
`btnPosition=end` — значения позиции только `start`/`center`/`end`, шаблон
подставляет их в `md:justify-*`, `blank=false`).

Форма создания Post/Project стартует с блока `title` в main-секции
(`Builder::make('main_section')->default(Title::getDefaultBlock())`, `h2`,
`position=center`), поэтому секция при создании практически всегда непуста —
`related-thematic` и `category-list` теперь дописываются почти всегда (раньше
запись без блоков не получала ни того, ни другого). Пустым блок не остаётся:
заголовок записи проливается в него на лету, см. [file-map.md](file-map.md#блоки).

## Формы (custom form builder)

- `Form` описывает форму (не путать с `Filament\Schemas\Form`).
- `FormField` — поля формы (label — longtext, есть кастомные типы:
  `text`, `email`, `phone`, `textarea`, `select`, `radio`, `checkbox`, `file`).
- Публичный рендер — `App\Livewire\Forms\PublicForm`.
- Действие отправки — `App\Actions\Forms\SubmitFormAction` (rate-limit 5/300с/IP).
- Специализированные сервисы — `app/Services/Forms/` (rules, attributes,
  submission creator, file storer, data normalizer, email template renderer).
- Отправка писем — асинхронный `App\Jobs\SendFormSubmissionEmails` (tries=5).
  Оба письма, админское и клиентское, заворачиваются в брендовый шаблон
  `emails/email-template.blade.php`; голые вью `form-submission-*` остались
  запасным путём на случай пустых темы или тела.
- **Адрес клиента** ищется по порядку: ключ `email`, ключ `user_email`, затем
  первое включённое поле формы с `type = 'email'` — назвать поле могут как
  угодно, тип надёжнее имени. Если адреса нет, а тумблер включён, заявка уходит
  в `Failed` с причиной в `error_message`, а не помечается доставленной.
- Тумблер «письмо пользователю» блокируется, если у формы нет включённого поля
  типа Email (`FormForm::hasEmailField()`).
- **Два канала диагностики в джобе.** `$skipped` — письмо было некуда слать,
  заявка уходит в `Failed`. `$notes` — письмо ушло, но не таким, как настроено
  (пропали вложения): статус остаётся `Sent`, потому что `Failed` спровоцировал бы
  переотправку, а это дубль (баг #2). Оба списка склеиваются в `error_message`,
  и он показывается на странице заявки.
- **`Form` использует `TracksMediaUsage`** — иначе вложения писем
  (`user_mail_attachments`) не попадают в `media_file_usages`, и медиа-менеджер
  предлагает удалить их как неиспользуемые.
- **`Reply-To` глобальный** — `config('mail.reply_to')` из
  `MAIL_REPLY_TO_ADDRESS`. Отправляем с `post@niipigrad.ru`, а читают ответы в
  другом ящике, поэтому адрес ответа задан отдельно и висит на всех письмах,
  включая системные. Пустая переменная — заголовка нет.

## Публичные запросы

- `App\Services\NewsQuery` — новости.
- `App\Services\ProjectsQuery` — проекты.
- `App\Services\ContentRenderer` — сборка страничного контента (диспатч блоков).

## Роли и авторизация

- `UserRole` (Admin/Editor/Viewer) закрывает видимость ресурса через
  `RoleAccessResource`.
- Реальная авторизация действий — политики `App\Policies\*` c `BasePolicy::before()`,
  где Admin получает bypass. См. [patterns/role-access-resource.md](patterns/role-access-resource.md).
