# Доменная модель

## Сущности (`app/Models/`)

- **Page** — статические/динамические страницы (`/{slug}`, `/`). Реализует
  `HasBlockSections`, `HasMeta`, использует `HasSectionOptions`.
- **Post** — новости (`/news/{slug}`). Пивот `category_post`. Флашит теги кэша
  `['news','categories']`.
- **Project** — проекты (`/projects/{slug}`). Пивот `category_project`.
  Флашит теги `['projects','categories']`.
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
    - `options` (json — `[{value, label, default?, disabled?}, ...]`; `disabled` работает только для `select` (`<option disabled>`); пустой `value` допустим **только** при `disabled: true` — паттерн плейсхолдера с сохранением HTML5 `required`)
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
| `CategoryStatus`       | Draft/Published(=`active`)/Archived — **value `Published` = `'active'`** | Category            |
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

## Формы (custom form builder)

- `Form` описывает форму (не путать с `Filament\Schemas\Form`).
- `FormField` — поля формы (label — longtext, есть кастомные типы:
  `text`, `email`, `phone`, `textarea`, `select`, `radio`, `checkbox`, `file`).
- Публичный рендер — `App\Livewire\Forms\PublicForm`.
- Действие отправки — `App\Actions\Forms\SubmitFormAction` (rate-limit 5/300с/IP).
- Специализированные сервисы — `app/Services/Forms/` (rules, attributes,
  submission creator, file storer, data normalizer, email template renderer).
- Отправка писем — асинхронный `App\Jobs\SendFormSubmissionEmails` (tries=5).

## Публичные запросы

- `App\Services\NewsQuery` — новости.
- `App\Services\ProjectsQuery` — проекты.
- `App\Services\ContentRenderer` — сборка страничного контента (диспатч блоков).

## Роли и авторизация

- `UserRole` (Admin/Editor/Viewer) закрывает видимость ресурса через
  `RoleAccessResource`.
- Реальная авторизация действий — политики `App\Policies\*` c `BasePolicy::before()`,
  где Admin получает bypass. См. [patterns/role-access-resource.md](patterns/role-access-resource.md).
