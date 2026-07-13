# Доменная модель

## Сущности (`app/Models/`)
- **Page** — статические/динамические страницы (`/{slug}`, `/`).
- **Post** — новости (`/news/{slug}`).
- **Project** — проекты (`/projects/{slug}`).
- **Category** — категории (типизируется `CategoryType`, статус `CategoryStatus`).
- **Menu** — навигационные меню.
- **Footer** — футер сайта (структура).
- **GlobalSetting** — глобальные настройки.
- **Form** — конфиг формы (custom form builder).
- **FormField** — поле формы (label в longtext).
- **FormSubmission** — заявка/отправка формы (со статусом).
- **FormSubmissionFile** — вложение к заявке.
- **User** — пользователь админки (роль `UserRole`).

Общие concerns — `app/Models/Concerns/` (напр. `HasSectionOptions`).

## Enums (`app/Enums/`)
| Enum | Применяется к |
|---|---|
| `PageStatus` | Page |
| `PostStatus` | Post |
| `ProjectStatus` | Project |
| `CategoryStatus` | Category |
| `CategoryType` | Category (различает тематику категории) |
| `FormApplicantType` | Form (тип заявителя) |
| `FormSubmissionStatus` | FormSubmission |
| `UserRole` | User |

## Блочный контент
Page/Post/Project содержат последовательность блоков. Каждый блок имеет
тип, соответствующий классу в `app/Blocks/Renderers/` — список типов см. в
[file-map.md](file-map.md#блоки).

## Формы (custom form builder)
- `Form` описывает форму (title, applicant_type, success_message,
  user_mail_attachments и т.д.).
- `FormField` — поля формы (label — longtext, есть кастомные типы).
- Отправка сохраняется в `FormSubmission` + `FormSubmissionFile` для файлов.
- Публичный рендер — `App\Livewire\Forms\PublicForm`.
- Action-логика — `app/Actions/Forms/`, сервисы — `app/Services/Forms/`.

## Публичные запросы
- `App\Services\NewsQuery` — выборки новостей.
- `App\Services\ProjectsQuery` — выборки проектов.
- `App\Services\ContentRenderer` — сборка страничного контента.