# Чек-лист: баги и cleanup

Детали и обоснования — в [bugs.md](bugs.md). Здесь только галочки по
**открытым** пунктам. Закрытые (#1–#12, #15, #17–#21) переехали в
[archived/bugs.md](archived/bugs.md).

P0, P1 и P2 закрыты целиком.

## P3 — cleanup

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
