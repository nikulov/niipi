# Чек-лист: баги и cleanup

Детали и обоснования — в [bugs.md](bugs.md). Здесь только галочки по
**открытым** пунктам. Закрытые (#1, #3–#7, #10, #12, #15, #17–#21)
переехали в [archived/bugs.md](archived/bugs.md).

## P0

- [ ] **#2** `SendFormSubmissionEmails` не идемпотентен → дубли админу,
      падение одного плеча глушит второе, статус врёт.
      `app/Jobs/SendFormSubmissionEmails.php:96`
      План отложен: [form-mail-idempotency](form-mail-idempotency/README.md)

## P1

_(пусто — все закрыты)_

## P2 — латентные

- [ ] **#8** `FormRulesBuilder::filterMimesRules` сносит валидный
      `mimes:*` вместе с `mimetypes:*` (латентно: UI скрывает rules для
      file-полей).
      `app/Services/Forms/FormRulesBuilder.php:148`
- [ ] **#9** `AbstractContentFull::mount` не типизирует `categoryIds` до
      int'ов (латентно: Filament хранит int-ключи).
      `app/Livewire/Components/AbstractContentFull.php:37`

## P3 — cleanup

- [ ] **#11** Опечатка `AuthServiceProvoider` + мёртвый `$policies`
      (документировано в `.ai/decisions.md`).
      `app/Providers/AuthServiceProvoider.php`
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
