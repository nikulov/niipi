# Чек-лист: баги и cleanup

Детали и обоснования — в [bugs.md](bugs.md). Здесь только галочки.

## P0 — активные баги

- [ ] **#1** `FormRulesBuilder::parseExtraRules` роняет list-form правила
      → пользовательская валидация не срабатывает.
      `app/Services/Forms/FormRulesBuilder.php:170`
- [ ] **#2** `SendFormSubmissionEmails` не идемпотентен → админ получает
      дубли писем при retry.
      `app/Jobs/SendFormSubmissionEmails.php:96`
- [ ] **#3** `SubmitFormAction` оставляет осиротевшие файлы при откате
      DB-транзакции.
      `app/Actions/Forms/SubmitFormAction.php:57` + `SubmissionFilesStorer.php:45`

## P1 — активные, узкие

- [ ] **#4** `HasSectionOptions::getSectionOption` возвращает null от
      первого пустого блока-настройки; второй такой же блок недостижим.
      `app/Models/Concerns/HasSectionOptions.php:29`
- [ ] **#5** `SubmissionsStats` «Новых сегодня» считает все статусы, не
      только `New`.
      `app/Filament/Widgets/SubmissionsStats.php:22`
- [ ] **#6** `SubmitFormAction::handle` — update статуса вне транзакции
      → orphan state при сбое БД между commit и update.
      `app/Actions/Forms/SubmitFormAction.php:67`
- [x] **#15** Счётчики категорий и «Все» в `NewsFull`/`ProjectsFull` не
      фильтруют по `published_at <= now()` → расхождение с выборкой
      карточек после фикса 91c28d2.
      `app/Livewire/Components/{NewsFull,ProjectsFull}.php:27-30`
      + `AbstractContentFull.php:127-134`

## P2 — латентные

- [ ] **#7** Type-hint `Post $post` в forceDelete/forceDeleteAny в 11
      полиси (латентно: SoftDeletes нигде не используется).
- [ ] **#8** `FormRulesBuilder::filterMimesRules` сносит валидный
      `mimes:*` вместе с `mimetypes:*` (латентно: UI скрывает rules для
      file-полей).
      `app/Services/Forms/FormRulesBuilder.php:148`
- [ ] **#9** `AbstractContentFull::mount` не типизирует `categoryIds` до
      int'ов (латентно: Filament хранит int-ключи).
      `app/Livewire/Components/AbstractContentFull.php:37`

## P3 — cleanup

- [ ] **#10** Пустой try/catch в `PublicForm::submit`.
      `app/Livewire/Forms/PublicForm.php:99`
- [ ] **#11** Опечатка `AuthServiceProvoider` + мёртвый `$policies`
      (документировано в `.ai/decisions.md`).
      `app/Providers/AuthServiceProvoider.php`
- [ ] **#12** `ProjectObserver::saving(Project $post)` — переименовать
      параметр в `$project`.
      `app/Observers/ProjectObserver.php:10`
- [ ] **#13** `CategoryStatus::Published = 'active'` расходится с
      `'published'` в `Post/Page/ProjectStatus`.
      `app/Enums/CategoryStatus.php:10`
- [ ] **#14** `FormEmailTemplateRenderer` — теоретическая коллизия имён
      data/file полей (файл перезаписывает текст).
      `app/Services/Forms/FormEmailTemplateRenderer.php:78`
- [ ] **#16** Счётчики категорий stale до 10 мин после наступления
      `published_at` по расписанию — кэш `getCategories()` (TTL 600с)
      инвалидируется только на `saved`/`deleted`.
      `app/Livewire/Components/AbstractContentFull.php:83-90`

## Исправлено в ревью 2026-08-04

Коммиты `4e2e324`, `16c934f`.

- [x] **#17** Кнопка «Смотреть все» в `related-thematic` строила URL по
      первой категории записи и игнорировала override `data.categoryIds`
      → кнопка удалена.
- [x] **#18** Плейсхолдер-опция (пустой `value` + `disabled`) протекала в
      `radio` и рендерилась выбираемой радиокнопкой с пустым значением.
- [x] **#19** Дефолт `radio` жил только в state — в разметке не было
      `@checked`, ничего не выглядело отмеченным.
- [x] **#20** Несколько `default: true`: `extractDefault` брала первую,
      `@selected` помечал все → DOM расходился со state.
- [x] **#21** Опция с `value: "0"` не могла быть дефолтом — `!empty("0")`.
