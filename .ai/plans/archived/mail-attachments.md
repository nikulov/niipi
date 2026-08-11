# Вложения писем теряются молча

Сделано 2026-08-11. Было: `buildFormUserAttachments()` выбрасывал пути, которых
нет на диске, письмо уходило без вложения, заявка получала `Sent`, в логах пусто.

## Почему файл пропадал

`Form` не использовал `TracksMediaUsage` — трейт был только у `Footer`,
`GlobalSetting`, `Post`, `Page`, `Project`. Из-за этого `MediaFileUsage` для
формы не создавался, и в медиа-менеджере вложение выглядело неиспользуемым:
`usages_count = 0`, фильтр «не используется» его показывал, модалка удаления
молчала, а `->before()` физически стирал файл с диска. Дальше джоб молча его
отфильтровывал. То есть удаление выглядело безопасным ровно потому, что связь
не отслеживалась.

## Что сделано

- **`TracksMediaUsage` на `App\Models\Form`.** `MediaUsageService::extractPaths()`
  разбирает json-атрибуты рекурсивно и фильтрует по расширению, поэтому
  `user_mail_attachments` попал в usages без спецкода. `MediaSyncCommand` ищет
  модели по трейту, дописывать её не пришлось.
- **`buildFormUserAttachments()` возвращает `[$attachments, $missing]`** (через
  `partition`). Пропавшие пути идут в `Log::warning('Form user mail attachments
  are missing', [...])` и в `error_message` заявки.
- **Статус остаётся `Sent`.** Письмо клиенту ушло; `Failed` спровоцировал бы
  переотправку из админки, а это дубль — баг #2 не чинен. Для этого в джобе
  два списка: `$skipped` (роняет в `Failed`) и `$notes` (только текст).
- **`error_message` виден на странице заявки** — `TextEntry` с `color('danger')`,
  показывается когда поле не пусто. Раньше поле писалось, но нигде не выводилось,
  так что вся диагностика оставалась в логах.
- Ключи `panel.mail_attachments_missing` и `panel.error_message` в обоих языках;
  заодно в `lang/en/panel.php` добавлены `mail_skipped_*`, которых там не было.

## Тесты

`SendFormSubmissionEmailsTest`:

- `test_missing_attachment_is_reported_but_the_letter_still_goes_out` — письмо
  уходит с одним вложением из двух, лог получает пропавший путь, `error_message`
  называет только его, статус `Sent`.
- `test_form_registers_its_mail_attachments_in_the_media_library` — запись в
  `media_file_usages` появляется при сохранении формы. Проверено мутацией: без
  трейта тест падает.

## Хвост — выкатка

После деплоя один раз прогнать на сервере, чтобы проставить usages уже
существующим формам:

```
php artisan media:sync --usages-only
```

Локально прогнано — четыре записи по двум формам (`user_mail_attachments`).
Без этого прошлые вложения останутся «неиспользуемыми» до первого сохранения
формы в админке.
