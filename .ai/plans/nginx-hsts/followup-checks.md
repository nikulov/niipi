# Проверки через сутки-двое после раскатки 2026-07-30

Цель — убедиться, что новый nginx-конфиг стабилен, ничего не сломал
в проде, и решить по остаточным пунктам (подъём stage `max-age`,
уборка старого сертификата).

**Ориентир по срокам:** запустить эти проверки не раньше **2026-08-01**
(сутки после раскатки) и не позже **2026-08-06** (пока свежо в памяти).

## Прогон 2026-08-05 — итог

Прогнано 5 из 6 проверок, все чисто. Конфиг стабилен, регрессий нет.

| # | Проверка | Итог |
| --- | --- | --- |
| 1 | nginx error.log | ✅ ни одного emerg/alert/warn |
| 2 | HTTP-ответы | ✅ совпадают с 2026-07-30 |
| 3 | TLS-версии | ✅ 1.2/1.3 работают, 1.0/1.1 отбиты на prod и stage |
| 4 | SSL Labs | ✅ **A+**, `hasWarnings: false`, `isExceptional: true` |
| 5 | OPcache / `$realpath_root` | ⛔ **не проверено** — деплоев с 15.07 не было |
| 6 | Жалобы / всплеск ошибок | ✅ ни одной ошибки, не объяснённой известными багами |

**Единственный хвост — пункт 5.** Прод крутит релиз `20260715142417`
(15 июля), то есть **ни одного деплоя после правки nginx 30.07**.
`$realpath_root` проверяется только следующей выкаткой. Риск при этом
низкий: `opcache.validate_timestamps=On`, `revalidate_freq=2` — файлы
перечитываются по mtime даже без правильного ключа пути.

Заодно: фиксы #24 и #26 закоммичены 05.08, но **на проде их нет** —
последний релиз старше обоих.

## Обязательные проверки

### 1. Nginx error.log за период после раскатки

```
ssh -i ~/.ssh/niipi-prod root@89.108.113.198 \
  'tail -200 /var/log/nginx/error.log && echo "---" && \
   grep -iE "emerg|alert|crit|warn" /var/log/nginx/error.log | tail -30'
```

Ищем: emerg/crit/alert (не должно быть вообще), warn (должно быть
пусто по нашим блокам — только certbot-renewal нормально).

**Плохие сигналы:** `SSL_do_handshake() failed`, `upstream timed out`,
`open() ... failed`, `permission denied`. Разбираться до похода дальше.

**Результат 2026-08-05 — ✅ чисто.** Ни одного из четырёх плохих
сигналов за всю историю логов (проверено с ротацией, `error.log*`).
`emerg` / `alert` / `warn` — по нулям. Что есть, разобрано:

- **936 × `access forbidden by rule`** — это наш dotfile-deny работает.
  Боты долбятся в `/.git/config`, `/.env`, `/.tmb/*.php`. Ожидаемое
  поведение, а не поломка.
- **14 × `directory index of "…/public/images/" is forbidden`** —
  `autoindex off`, отдаётся 403. Поведение доnginx-правки, не наше.
- **8 × `[crit] SSL_read() failed … SSL alert number 121`** — единственный
  crit. Все восемь с `141.76.94.17` / `141.76.94.23` (сеть TU Dresden,
  академический TLS-сканер) и с `server: 0.0.0.0:443`, то есть в
  default-блок без SNI. Алерт 121 в IANA-реестре не назначен — сканер
  шлёт нестандартное и рвёт соединение. Последний — 04.08 01:08, после
  раскатки catch-all такие вообще не долетают (рукопожатие рвём раньше).
  Пользователей не касается, чинить нечего.
- **2 × `recv() failed (104: Connection reset by peer) … from upstream`** —
  оба 30.07 21:55:13, запросы `GET /feed/` и `GET /comments/feed/`
  (HTTP/1.0). Это баг #27, PHP-FPM сбросил соединение; в access.log им
  соответствуют единственные два 502. Ни разу не повторилось.

### 2. HTTP-ответы сохранились

Полный verification-скрипт из README:

```
for url in \
  http://niipigrad.ru/ \
  http://www.niipigrad.ru/ \
  https://www.niipigrad.ru/ \
  https://niipigrad.ru/ \
  https://niipigrad.ru/vendor/livewire/livewire.js \
  https://niipigrad.ru/favicon.ico \
  https://niipigrad.ru/storage/.test-should-be-denied \
  https://niipigrad.ru/wp-admin.php \
  http://stage.niipigrad.ru/ \
  https://stage.niipigrad.ru/ ; do
    echo "=== $url"
    curl -skI "$url" | grep -Ei '^(HTTP|Location|Strict-Transport|X-Frame|X-Content|Referrer)'
done
```

Все ответы должны совпадать с состоянием на 2026-07-30 (см. README).

**Результат 2026-08-05 — ✅ совпадают.** Все HTTPS-ответы `HTTP/2` с
HSTS + тремя security-headers, включая 403 из `/storage/.dotfile` и 404
из `/wp-admin.php`. HTTP-редиректы — без HSTS, по RFC 6797. Stage —
`HTTP/2 200 + max-age=300`. Добавлен `https://niipigrad.ru/contacts`
(200) — страница из багов #23/#24.

**Ловушка в самом списке:** `https://niipigrad.ru/vendor/livewire/livewire.js`
отдаёт **404 — и это норма**. Livewire-ассеты не публиковались, в
`public/` каталога `vendor/` нет вовсе, а `location ^~ /vendor/livewire/`
делает `try_files $uri =404`. Реальный ассет страница берёт по адресу
**`/livewire/livewire.min.js?id=…`** — его обслуживает `location ^~
/livewire/` через `try_files … /index.php`, и он отдаёт `200`,
`application/javascript`, 152 666 байт, `Cache-Control: max-age=31536000`
плюс все security-headers. `POST /livewire/update` → `419` (маршрут жив,
CSRF на месте). Проверять надо `.min.js`, иначе 404 читается как поломка
публичной формы.

### 3. TLS-версии на месте

```
echo | openssl s_client -connect niipigrad.ru:443 -servername niipigrad.ru -tls1_2 2>/dev/null | grep Protocol
echo | openssl s_client -connect niipigrad.ru:443 -servername niipigrad.ru -tls1_1 2>&1 | grep -E "alert|no protocols"
```

TLS 1.2 — работает. TLS 1.1 — отклоняется.

**Результат 2026-08-05 — ✅.** TLS 1.2 (`ECDHE-RSA-AES256-GCM-SHA384`) и
TLS 1.3 (`TLS_AES_256_GCM_SHA384`) поднимаются. TLS 1.1 и 1.0 —
`no protocols available`, и на prod, и на **stage** (глобальный
`nginx.conf` cleanup из пункта D держит). Без SNI — `tlsv1 unrecognized
name`, alert 112: catch-all `ssl_reject_handshake` на месте.

Сертификаты заодно: prod — GlobalSign `*.niipigrad.ru` + apex в SAN,
RSA 2048 / SHA-256, цепочка до `GlobalSign Root CA - R6`,
`Verify return code: 0 (ok)`, годен до **2027-03-06**. Stage — Certbot,
годен ещё 52 дня, `certbot.timer` активен (последний прогон 05.08 09:28).
`certbot certificates` знает только `stage.niipigrad.ru` — удаление
`prod2` из пункта C подтверждено.

### 4. SSL Labs grade (внешний прогон)

Открыть в браузере: https://www.ssllabs.com/ssltest/analyze.html?d=niipigrad.ru&hideResults=on

Ожидаемо после наших правок — **A** или **A+** (было бы **B** из-за
TLS 1.0/1.1, если бы не поправили). Grade **T** или ниже — красный
флаг, срочно разбираться.

**Результат 2026-08-05 — ✅ A+.** Прогон через API
(`api.ssllabs.com/api/v3/analyze?host=niipigrad.ru&publish=off`, в
публичные доски не попадает). Endpoint `89.108.113.198`:
`grade: A+`, `gradeTrustIgnored: A+`, `hasWarnings: false`,
`isExceptional: true`. Протоколы — ровно TLS 1.2 + 1.3. Уязвимости:
BEAST / Heartbleed / POODLE / FREAK / Logjam / RC4 — все `false`.
`forwardSecrecy: 4` (со всеми браузерами), ALPN есть, HSTS
`present, max-age=31536000, includeSubDomains, preload: null` — ровно
то, что решали.

`ocspStapling: false` — знаем, stapling сознательно вне scope (нужен
`resolver`). На оценку не влияет, A+ получен без него.

### 5. OPcache: релиз реально подхватывается

Проверить после следующего деплоя (или создать тестовый деплой):
- Сделать редакцию в `resources/views/*.blade.php` — что-то заметное.
- Задеплоить через обычный процесс (симлинк `current/` переключается).
- Проверить, что новая версия видна в браузере **без** `opcache_reset`
  или `systemctl reload php8.4-fpm`.

Если новая версия не видна — `$realpath_root` не сработал, разбираться
(проверить `phpinfo` на серверном пути к SCRIPT_FILENAME).

**Результат 2026-08-05 — ⛔ проверить нечем, единственный незакрытый пункт.**
`current` смотрит на `releases/20260715142417`, и это самый свежий каталог
в `releases/`. Деплоев после правки nginx 30.07 **не было ни одного**, а
`$realpath_root` проявляется только на переключении симлинка.

Смягчает: `opcache.enable=On`, но `opcache.validate_timestamps=On` и
`opcache.revalidate_freq=2`. То есть OPcache и так перечитывает файлы по
mtime раз в две секунды — даже если бы ключ пути остался старым, залипания
на неделю не вышло бы. `$realpath_root` тут за корректность, а не за
спасение от катастрофы.

Побочно всплыло: фиксы #24 (`#[Locked]`) и #26 (`inline_svg()`) закоммичены
05.08, но релиз на проде от 15.07 — **на бою их нет**. Проверку делать
первой же выкаткой.

### 6. Никаких жалоб пользователей / клиентов

Проверить:
- Support-каналы (если есть).
- Sentry/Bugsnag/логи Laravel — всплеск ошибок с 2026-07-30 20:33.
- `storage/logs/laravel.log` — что-то новое после этого времени?

Особенно важно: не отвалились ли какие-то `.php` пути. Ожидание —
нет, но если кто-то у нас через костыль вызывал не index.php
(маловероятно, но проверить).

**Результат 2026-08-05 — ✅ ни одной ошибки сверх известных багов.**

Разбор всех 5xx по access-логам за период с раскатки:

| Код | Раз | Что это |
| --- | --- | --- |
| 500 | 20 | баг #24, бот `45.225.135.213` через голый IP, 04.08 05:56 |
| 500 | 2 | баг #23, живой посетитель `89.22.52.119`, 04.08 13:34 |
| 502 | 2 | баг #27, `/feed/` и `/comments/feed/`, 30.07 21:55 |

Больше 5xx нет вообще. Все 24 объясняются багами, которые уже заведены и
к nginx-правке отношения не имеют. **За 05.08 — ни одной пятисотки**;
файла `laravel-2026-08-05.log` не появилось вовсе (последний —
`laravel-2026-08-04.log`), в нём ровно 20 записей `successMessage` + 2
`file_size … livewire-tmp` и ничего больше с 30.07 20:33.

`.php`-пути никого не сломали: PHP-restriction режет только то, что и
задумано (`/wp-admin.php` → 404), приложение живёт через
`location = /index.php`. Опасение из плана не подтвердилось.

Общий фон здоровый: 68 015 × 301 (редиректы работают), 11 165 × 200,
948 × 403 (dotfile-deny + autoindex). Доминирует 72 410 × 404 — это
целиком legacy-сканирование WordPress из багов #27/#28, к нам не относится.

**Support-каналы не проверялись** — их у меня нет. Если жалобы куда-то
приходят, свериться отдельно.

## Решения по остаточным пунктам

### A. ~~Подъём stage `max-age`~~ — не делаем (решение 2026-07-30)

Stage — тестовый стенд, никаких пользовательских данных не хранит,
защищать нечего. `max-age=300` оставляем как есть навсегда:
короткий срок сохраняет свободу манёвра, если TLS на stage вдруг
сломается. Возвращаться к этому пункту не будем.

### B. Подача на hstspreload.org — **отложено бессрочно**

Решение 2026-07-30: сейчас не подаём. Причины уже задокументированы
(необратимо 6-12 мес, требует стабильности stage-TLS и любых будущих
поддоменов). Если когда-то захотим — процедура: вернуть `preload`
в HSTS-строку в 5 местах prod-конфига → nginx -t → reload →
подать на https://hstspreload.org. Отдельное осознанное решение,
не автоматически.

### C. ~~Уборка `prod2.niipigrad.ru` сертификата~~ — сделано 2026-07-30

Удалён через `certbot delete --cert-name prod2.niipigrad.ru`.
Бэкап на всякий случай: `/root/backup/prod2-letsencrypt.2026-07-30.tar.gz`
(содержит live/ + archive/ + renewal/). Certbot теперь управляет
только `stage.niipigrad.ru`. Основной wildcard prod-серт вне зоны
Certbot (`/etc/ssl/prod/_.niipigrad.ru.*`) — не тронут.

~~Побочно замечены stale .bak-файлы в `/etc/nginx/sites-available/`.~~
Убраны 2026-07-30: `mv` в `/root/backup/nginx-sites-available-bak/`
(3 файла: `niipigrad-prod.bak-20260525-{155818,160150,160508}`).
`sites-available/` теперь содержит только `default`, `niipigrad-prod`,
`niipigrad-stage`. `nginx -t` после уборки — чисто, reload не нужен
(в `sites-enabled` ничего не менялось).

### D. ~~Глобальный `nginx.conf` cleanup~~ — сделано 2026-07-30

В `/etc/nginx/nginx.conf` строки 33-34 обновлены:
- Было: `ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;` + `ssl_prefer_server_ciphers on;`
- Стало: `ssl_protocols TLSv1.2 TLSv1.3;` + `ssl_prefer_server_ciphers off;`

Практического изменения в TLS-состоянии сайтов **нет** — все три
sites-enabled (`default` port 80 only, `niipigrad-prod` explicit,
`niipigrad-stage` через Certbot-include) уже переопределяли globals
своими значениями. Правка — defense-in-depth для будущих сайтов,
чтобы забытое `ssl_protocols` не откатывалось к TLSv1/1.1.

Бэкап: `/root/backup/nginx.conf.pre-tls-cleanup.2026-07-30-2058.conf`.
Regression проверена: TLS 1.2 работает и на prod, и на stage;
TLS 1.1 отклоняется на обоих; оба сайта возвращают HTTP/2 200.

При следующем `apt upgrade nginx` dpkg спросит про изменённый
config-файл — оставить свою версию.

## Если что-то сломалось — откат

Все три бэкапа в `/root/backup/`:

```
# Откат prod:
ssh ... 'cp /root/backup/niipigrad-prod.pre-deploy.2026-07-30-2033.conf \
             /etc/nginx/sites-available/niipigrad-prod && \
         nginx -t && systemctl reload nginx'

# Откат stage HSTS:
ssh ... 'cp /root/backup/niipigrad-stage.pre-hsts.2026-07-30-2038.conf \
             /etc/nginx/sites-available/niipigrad-stage && \
         nginx -t && systemctl reload nginx'
```

## После завершения followup'а

Выполнено 2026-08-05:

- [x] Отметить все проверки в этом файле как выполненные — см. итоговую
      таблицу наверху и разбор под каждым пунктом.
- [x] Подъём stage `max-age` (пункт A) и чистка `prod2` (пункт C) —
      решения приняты ещё 30.07, новых заходов не требуют.
- [ ] Архивация плана. **Пока нельзя:** висит пункт 5 (OPcache /
      `$realpath_root`), он проверяется только следующей выкаткой. До
      тех пор план живёт в `plans/plan.md → Wrapping up`.

Наблюдение мимо чек-листа: 05.08 в 02:19 пришёл запрос с
`host: "prod2.niipigrad.ru"` — DNS-запись на поддомен всё ещё
существует, хотя сертификат удалён 30.07. Теперь такие соединения рвутся
на `ssl_reject_handshake` из catch-all. Ничего не ломает, но если запись
никому не нужна — её стоит убрать из DNS отдельным заходом.
