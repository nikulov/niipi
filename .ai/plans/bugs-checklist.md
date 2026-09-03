# Чек-лист: баги и cleanup

Только галочки по **открытым** пунктам. Детали и трассы — в трёх файлах:
[bugs.md](bugs.md) (#22, #42, #43, #44), [bug-report-2026-08-04.md](bug-report-2026-08-04.md)
(#23–#40, снято с логов прода) и
[bug-report-2026-08-06.md](bug-report-2026-08-06.md) (#41). Закрытые #1–#21 —
в [archived/bugs.md](archived/bugs.md); нумерация сквозная, номера не
переиспользуются.

## P0 — ломает пользовательский поток

- [x] **#41** Почта не уходила с прода вообще: SMTP рвался на проверке TLS —
      `mail.niipigrad.ru` отдавал чужой сертификат `CN=*.hosting.reg.ru`.
      Закрыт 2026-08-10: хостер выписал SSL на `mail.niipigrad.ru`,
      `MAIL_HOST` менять не пришлось. Отправка проверена — джобы `DONE`
      07.08 и 10.08. Письма заявки 1108 остались в `failed_jobs` (см. #40).
- [x] **#23** Публичная форма падает 500 при отправке с файлом
      (`UnableToRetrieveMetadata` на `livewire-tmp`). 14 раз, из них
      **2 живых 04.08** — посетитель дважды не смог отправить заявку.
      Воспроизведён и закрыт 2026-08-21 (`7e18c0b`): отсев мёртвых файлов в
      `PublicForm` + `FilesystemException` вокруг `validate()`. Источник пустого
      пути — неудачная запись, отвечающая `200 {"paths":[""]}`. Разбор —
      [public-form-crashes](public-form-crashes/README.md).
- [x] **#24** `Undefined array key "successMessage"` → 500 при рендере
      публичной формы. 28 раз, **20 из них за две секунды 04.08**.
      Исправлен 2026-08-05: `#[Locked]` на серверные свойства `PublicForm`
      + guard в шаблоне. `resources/views/livewire/forms/public-form.blade.php:55`
- [x] **#43** Кастомный `regex:` из админки складывается с маской телефона —
      формы с полем «Телефон» не отправлялись вообще (12 из 13 на проде).
      Заведён и закрыт 2026-09-03: данные вычищены на проде и stage, `phone`
      в `FormRulesBuilder` больше не берёт правила из `FormField::rules`,
      редактор правил у `phone` скрыт в админке. Разбор — [bugs.md](bugs.md).

## P1 — активные, узкий сценарий или фон

- [x] **#25** Сайт открывается по голому IP, HTTP не редиректится на HTTPS —
      мимо HSTS и канонизации. Отсюда пришли все 20 пятисоток из #24.
      Закрыт 2026-08-05 раскаткой catch-all `zz-catch-all` на проде: 80 → 301
      на домен, 443 → `ssl_reject_handshake`. См. [nginx-hsts](archived/nginx-hsts.md)
- [x] **#26** `file_get_contents` по абсолютному URL в Blade — сервер ходит
      HTTP-запросом сам к себе через DNS. 10 падений на морганиях резолвера.
      Исправлен 2026-08-05: хелпер `inline_svg()` читает файл
      локально. `resources/views/components/other/social-icon.blade.php`
- [ ] **#27** Legacy WordPress-фиды `/feed/` и `/comments/feed/` — 16 146
      ошибок 404 за двое суток, это 80 % всех 404.
- [ ] **#28** `/cat/news/` — 404, 202 раза за двое суток. Успешных ответов
      на `/cat/*` нет вообще.
- [ ] **#29** `/sitemap.xml` — 404 на проде, в `robots.txt` нет строки
      `Sitemap:`. В репозитории и то и другое есть — не выкачено.
- [ ] **#30** `favicon.ico` нулевого размера, отдаётся с кодом 200.

## P2 — админка, редкий трафик, историческое

- [ ] **#31** Filament Notifications: TypeError при гидрации
      (`$notification must be of type array, int given`). **69 раз** — самая
      массовая ошибка в логах; после релиза 15.07 не повторялась.
- [ ] **#32** `Cannot update locked property` — 13 раз.
- [ ] **#33** `Cannot assign array to property Notifications::$isFilamentNotificationsComponent`
      — 4 раза.
- [ ] **#34** Livewire `corrupt data when trying to hydrate a component` —
      13 раз.
- [ ] **#35** `Unable to find component` — остатки Jetstream/Fortify
      (11 компонентов), 22 раза, все 11.06.
- [ ] **#36** `An action tried to resolve without a name` — 3 раза.
- [ ] **#37** Вызовы Livewire v2 API (`emit`, `dispatchBrowserEvent`) на
      компонентах v3 — 2 раза.
- [ ] **#38** Блок `form` на `page:1035` ссылается на удалённую форму — 1 раз.
- [ ] **#39** `Handler for event system does not exist` — 1 раз.
- [x] **#40** Десять потерянных писем по заявкам в `failed_jobs` (7 таймаутов
      job + 1 коннект-таймаут 03.03 + 2 от #41 за 06.08). Закрыт 2026-08-11:
      решено не оживлять — письма протухли, по 1108 был бы дубль.
      `queue:flush` на проде выполнен, `queue:failed` пуст.
- [ ] **#44** 419 «Страница устарела» на публичной форме: вкладка старше
      `SESSION_LIFETIME=120` теряет введённое на отправке. Один живой
      посетитель 28.08 так и не отправил заявку. Кэша страниц нет, дело в
      сроке жизни сессии. Разбор — [bugs.md](bugs.md).
- [x] **#42** Неудачная запись вложения заявки теряется молча: заявка принята,
      строка `FormSubmissionFile` с правдоподобным путём, файла нет.
      `store()` проверить нельзя — Livewire выбрасывает результат `put()`.
      Заведён и закрыт 2026-08-21: `exists()` после `store()` в
      `SubmissionFilesStorer` + ошибка на поле. Разбор — [bugs.md](bugs.md).

## P3 — cleanup

- [x] **#22** Двойное экранирование в текстовой части письма (`&amp;amp;`).
      Тема письма — `7c82673`, тело — 2026-08-11: `renderBodyText` с
      `escape: false` + `{!! $textBody !!}` в `emails/plain-text.blade.php`.
      Разбор — [archived/bugs.md](archived/bugs.md).
