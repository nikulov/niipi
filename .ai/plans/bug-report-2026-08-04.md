# Баг-репорт с продакшена — 2026-08-04

Собрано чтением логов прода (`root@89.108.113.198`), только чтение — ничего
не менялось, не чистилось, не перезапускалось.

Нумерация сквозная с [bugs.md](bugs.md) и [archived/bugs.md](archived/bugs.md),
последний занятый номер там — #22, поэтому здесь с **#23**.

Это **только список симптомов**. Разбора причин и вариантов фикса тут нет.

## Что просмотрено

| Источник | Путь | Покрытый период |
| --- | --- | --- |
| Laravel | `shared/storage/logs/laravel-*.log` | 17 файлов, 2026-06-01 … 2026-08-04 |
| Worker | `shared/storage/logs/worker.log` | 2026-07-16 … 2026-08-03 |
| Nginx error | `/var/log/nginx/error.log*` | 14 суток (с ротированными) |
| Nginx access | `/var/log/nginx/access.log{,.1}` | 2026-08-03 … 2026-08-04 |
| PHP-FPM | `/var/log/php8.4-fpm.log` | с 2026-08-02 |
| Очередь | таблица `failed_jobs` | вся история |

**Всего в Laravel-логах: 195 записей `production.ERROR`.** PHP-FPM чистый
(только NOTICE о релоадах). Worker чистый — все `SendFormSubmissionEmails`
завершились DONE.

**Важный контекст:** прод сидит на релизе `20260715142417` (симлинк `current`,
15.07). Всё, что смёржено в `staging` после этой даты, на прод не выкачено —
часть пунктов ниже этим и объясняется.

## Легенда

- **P0** — активный баг, ломает пользовательский поток.
- **P1** — активный, но узкий сценарий либо фоновый эффект.
- **P2** — админка / редкий трафик / историческое.

---

## P0 — ломает пользовательский поток

### 23. Публичная форма падает 500 при отправке с файлом

**Симптом:** `League\Flysystem\UnableToRetrieveMetadata: Unable to retrieve
the file_size for file at location: livewire-tmp/livewire-tmp.`

**Трасса:** `app/Actions/Forms/SubmitFormAction.php:53` (`Validator->validate()`)
← `app/Livewire/Forms/PublicForm.php:85`.

**Частота:** 14 раз. 25.06 — 12 раз, **04.08 — 2 раза (13:34:01, 13:34:26)**.

**Живой сценарий 04.08:** реальный посетитель (`89.22.52.119`, Chrome 150,
HTTP/2) на `https://niipigrad.ru/contacts`. Файл долетел — два
`POST /livewire/upload-file` вернули 200. Следующий `POST /livewire/update`
(отправка формы) — 500. Через 25 секунд повторил — снова 500. Заявка не ушла.

---

### 24. `Undefined array key "successMessage"` при рендере публичной формы

**Симптом:** `Undefined array key "successMessage" (View:
resources/views/livewire/forms/public-form.blade.php)` → HTTP 500 на
`POST /livewire/update`.

**Место:** `resources/views/livewire/forms/public-form.blade.php:55` —
`{!! $viewData['successMessage'] !!}` читается без guard, тогда как соседние
обращения к `$viewData['fields']` идут через `?? []`.

**Частота:** 28 раз. 14.06 — 8 раз, **04.08 — 20 раз за две секунды
(05:56:02–05:56:03)**.

**Источник всплеска 04.08:** один IP `45.225.135.213`, 20 идентичных
`POST /livewire/update`, referer — `https://89.108.113.198/contacts`
(см. #25). Rate-limiter в `SubmitFormAction` (5 попыток / 300 с) на проде
присутствует, но все 20 запросов дошли до рендера и вернули 500.

**Статус в коде:** строка присутствует в текущем `staging` в том же виде.

---

## P1 — активные, узкий сценарий или фон

### 25. Сайт отдаётся по голому IP, HTTP не редиректится на HTTPS

**Проверено запросом с сервера:**

```
https://89.108.113.198/contacts  -> 200
http://89.108.113.198/           -> 200   (без редиректа на https)
```

По домену канонизация работает. Дефолтный vhost на IP отдаёт то же
приложение мимо HSTS и мимо канонических редиректов
(см. активный план [nginx-hsts](nginx-hsts/README.md)).

**Следствие:** 27 запросов за двое суток с IP-referer; **все 20 пятисоток
из #24 пришли именно оттуда** — на домен по 500-м приходится 2 из 22.

---

### 26. `file_get_contents` по абсолютному URL внутри Blade-компонента

**Симптом:** `file_get_contents(): php_network_getaddresses: getaddrinfo for
niipigrad.ru failed: Temporary failure in name resolution`.

**Место:** `resources/views/components/other/social-icon.blade.php` —
`{!! file_get_contents(public_asset($iconUrl)) !!}`. `public_asset()` отдаёт
абсолютный URL, то есть на каждый рендер иконки сервер ходит HTTP-запросом
сам к себе через DNS.

**Частота:** 10 раз (02.07 — 8, 09.07 — 1, 14.07 — 1) — каждый раз, когда
резолвер моргнул.

**Статус в коде:** в текущем `staging` строка та же.

---

### 27. Legacy WordPress-фиды дают 16 146 ошибок 404 за двое суток

| Путь | 404 за 03–04.08 |
| --- | --- |
| `/feed/` | 8215 |
| `/comments/feed/` | 7931 |

Это **80 % всех 404** (20 072) и заметно больше, чем весь успешный трафик
(1439 ответов 200 за 04.08). Плюс 7415 промежуточных 301 на те же пути
(`www` → канонический хост). Стучатся агрегаторы и читалки, оставшиеся с
WordPress-версии сайта.

---

### 28. `/cat/news/` — 404

202 ответа 404 за двое суток (+203 предшествующих 301 с `www`). Ответов 200
на `/cat/*` за этот период — ноль. Также прилетают `/cat/news/page/NN/`
и `/cat/news/feed`. Часть запросов идёт с внутренним referer вида
`https://www.niipigrad.ru/cat/news/page/27`.

---

### 29. `/sitemap.xml` — 404 на проде, `robots.txt` без строки Sitemap

**Проверено:** `https://niipigrad.ru/sitemap.xml` → 404. За двое суток
48 обращений от ботов (21 из них с referer самого сайта).

`robots.txt` на проде целиком:

```
User-agent: *
Disallow:
```

В репозитории `public/robots.txt:9` строка `Sitemap:` есть, маршрут
`routes/web.php:13` есть — но в выкаченном релизе `20260715142417` их нет
(фича закрыта 29–30.07, после даты релиза). См.
[archived/sitemap.md](archived/sitemap.md).

---

### 30. `favicon.ico` — файл нулевого размера

`current/public/favicon.ico` — 0 байт, отдаётся с кодом 200. За сутки
14 обращений к нему завершились 404 (через другой путь).

---

## P2 — админка, редкий трафик, историческое

### 31. Filament Notifications: TypeError при гидрации

`Filament\Notifications\Collection::{closure:...fromLivewire():32}():
Argument #1 ($notification) must be of type array, int given`

**Частота: 69 раз** — самая массовая ошибка за всю историю логов.
01.06 (18), 03.06 (8), 05.06 (3), 06.06 (3), 12.06 (12), 14.06 (1),
18.06 (2), 19.06 (2), 10.07 (1), 12.07 (19). После релиза от 15.07 не
повторялась.

---

### 32. `Cannot update locked property`

Свойства: `discoveredSchemaNames` (5), `userUndertakingMultiFactorAuthentication` (4),
`areSchemaStateUpdateHooksDisabledForTesting` (4).

**Частота:** 13 раз — 01.06 (6), 14.06 (1), 10.07 (6).

---

### 33. `Cannot assign array to property Notifications::$isFilamentNotificationsComponent of type bool`

**Частота:** 4 раза — 01.06 (2), 10.07 (2).

---

### 34. Livewire: `corrupt data when trying to hydrate a component`

`CorruptComponentPayloadException` в `Checksum.php:18`.

**Частота:** 13 раз — 12.06 (12), 29.06 (1).

---

### 35. `Unable to find component` — остатки Jetstream/Fortify

Отсутствующие компоненты: `profile.update-profile-information-form`,
`profile.update-password-form`, `profile.two-factor-authentication-form`,
`profile.logout-other-browser-sessions-form`, `profile.delete-user-form`,
`pages.auth.register`, `pages.auth.login`, `filament.pages.auth.register`,
`filament.pages.auth.login`, `auth.register`, `auth.login`.

**Частота:** 22 раза, все 11.06 — по 2 на каждый компонент.

---

### 36. `An action tried to resolve without a name`

View: `vendor/filament/actions/resources/views/components/modals.blade.php`.

**Частота:** 3 раза — 01.06 (2), 10.07 (1).

---

### 37. Вызовы Livewire v2 API на компонентах v3

`Unable to call component method. Public method [emit] not found on component`
и то же для `[dispatchBrowserEvent]`.

**Частота:** 2 раза, обе 10.07.

---

### 38. Блок `form` ссылается на удалённую форму

`Render failed {"section":null,"type":"form","model":"page:1035",
"error":"No query results for model [App\\Models\\Form]."}`

**Частота:** 1 раз, 05.06. Страница `page:1035` содержит блок формы,
указывающий на несуществующий `Form`.

---

### 39. `Handler for event system does not exist`

**Частота:** 1 раз, 10.07.

---

### 40. Восемь потерянных писем по заявкам в `failed_jobs`

| Когда | Исключение |
| --- | --- |
| 21.05, 22.05, 25.05 ×5 | `TimeoutExceededException: App\Jobs\SendFormSubmissionEmails has timed out` (7 шт.) |
| 03.03 13:00 | `TransportException: Connection could not be established with host "ssl://mail.niipigrad.ru:465"` |

Таблица `jobs` пуста, новых падений с 25.05 нет — но эти 8 записей висят
необработанными, письма по соответствующим заявкам не ушли.

---

## Шум — не баги

Зафиксировано, чтобы в следующий раз не разбирать заново.

- **4521 `access forbidden by rule`** в nginx error.log за 14 суток — сканеры
  ботов по `/.env` (183), `/.git/config` (104), `/.env.production`,
  `/.aws/credentials` и ещё ~40 вариантов. Правила из
  [nginx-hsts](nginx-hsts/README.md) отрабатывают штатно, это и есть
  ожидаемое поведение.
- **918 из 20 072 ошибок 404** — сканирование `wp-*`, `.env`, `.git`,
  `admin`, `xmlrpc`, Exchange-эндпоинтов (`/owa/`, `/ews/exchange.asmx`),
  подброс `*.php`-шеллов.
- **32 `directory index is forbidden`**, **8 `SSL_read() failed`**,
  **2 `recv() failed`** — фоновая ерунда за 14 суток, тренда нет.
- В access.log попадают строки с мусорным статусом (`166`, `"-"`, `SELECT`) —
  битые запросы от сканеров, а не ответы приложения.
