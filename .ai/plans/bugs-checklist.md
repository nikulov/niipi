# Чек-лист: баги и cleanup

Только галочки по **открытым** пунктам. Детали и трассы — в двух файлах:
[bugs.md](bugs.md) (#22) и [bug-report-2026-08-04.md](bug-report-2026-08-04.md)
(#23–#40, снято с логов прода). Закрытые #1–#21 — в
[archived/bugs.md](archived/bugs.md); нумерация сквозная, номера не
переиспользуются.

## P0 — ломает пользовательский поток

- [ ] **#23** Публичная форма падает 500 при отправке с файлом
      (`UnableToRetrieveMetadata` на `livewire-tmp`). 14 раз, из них
      **2 живых 04.08** — посетитель дважды не смог отправить заявку.
      `app/Actions/Forms/SubmitFormAction.php:53` ← `PublicForm.php:85`
      **Отложен 2026-08-05** — нет репро на пустой путь, непонятно, что
      чинить. См. [plan.md](plan.md) → «Отложено».
- [x] **#24** `Undefined array key "successMessage"` → 500 при рендере
      публичной формы. 28 раз, **20 из них за две секунды 04.08**.
      Исправлен 2026-08-05: `#[Locked]` на серверные свойства `PublicForm`
      + guard в шаблоне. `resources/views/livewire/forms/public-form.blade.php:55`

## P1 — активные, узкий сценарий или фон

- [x] **#25** Сайт открывается по голому IP, HTTP не редиректится на HTTPS —
      мимо HSTS и канонизации. Отсюда пришли все 20 пятисоток из #24.
      Закрыт 2026-08-05 раскаткой catch-all `zz-catch-all` на проде: 80 → 301
      на домен, 443 → `ssl_reject_handshake`. См. [nginx-hsts](nginx-hsts/README.md)
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
- [ ] **#40** Восемь потерянных писем по заявкам висят в `failed_jobs`
      (7 таймаутов + 1 `TransportException`). Новых падений с 25.05 нет,
      но эти письма не ушли.

## P3 — cleanup

- [ ] **#22** Двойное экранирование в текстовой части письма (`&amp;amp;`).
      Часть про тему письма исправлена (`7c82673`).
      `resources/views/emails/plain-text.blade.php`
