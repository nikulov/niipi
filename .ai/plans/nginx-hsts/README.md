# Nginx: HSTS и канонические редиректы

## Итог раскатки 2026-07-30

Раскатано на проде за одну сессию 20:33-20:38 (2026-07-30).
План разросся с «только HSTS» до полного nginx-хардненинга.

**Prod (`/etc/nginx/sites-enabled/niipigrad-prod`) — задеплоено:**
- HSTS `max-age=31536000; includeSubDomains` в 5 местах
  (main + `/vendor/livewire/` + `/vendor/filament/` + `/storage/`
  + статика + www-HTTPS-301). Без `preload` — осознанно.
- Security headers во всех тех же местах: `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`.
  `X-XSS-Protection` не ставим — deprecated.
- TLS `1.2/1.3` only + Mozilla intermediate ciphers (server-level, не
  из глобального `/etc/nginx/nginx.conf` — там до сих пор TLSv1/1.1).
- HTTP/2 (`listen ... ssl http2`).
- PHP restriction: `location = /index.php` — единственная точка exec,
  `location ~ \.php$ { return 404; }` — всё остальное.
- `/storage/` (public uploads): `^~` отменяет regex-исполнение PHP,
  nested `location ~ /\. { deny all; }` защищает от dotfile-утечек.
- `$realpath_root` для `SCRIPT_FILENAME`/`DOCUMENT_ROOT` — OPcache
  ключится на реальный путь релиза, атомарный swap `current/` работает.
- Regex-порядок: dotfiles-deny перед статикой (иначе `.hidden.js`
  утекал бы).
- `image/svg+xml` в `gzip_types`.

**Stage (`/etc/nginx/sites-enabled/niipigrad-stage`) — задеплоено:**
- HSTS `max-age=300` в основном HTTPS-server. Короткий max-age —
  чтобы не залипать при возможных проблемах TLS. На vendor/статике
  дублей нет (осознанно; при подъёме max-age — добавить).
- HTTP/2 в `listen 443` (sed'ом, чтобы убрать warning `protocol options
  redefined` после включения http2 на prod).

**Что _не_ трогали на stage:** security headers, PHP-restriction,
`/storage/`, `$realpath_root`, regex-порядок, gzip_types. Причина —
Certbot-managed listen'ы + минимальное касание тестового стенда.

**Бэкапы для отката** (`/root/backup/`):
- `niipigrad-prod.2026-07-30.conf` — исходное состояние до плана.
- `niipigrad-prod.pre-deploy.2026-07-30-2033.conf` — прямо перед деплоем.
- `niipigrad-stage.2026-07-30.conf` — исходное.
- `niipigrad-stage.pre-deploy.2026-07-30-2035.conf` — перед http2.
- `niipigrad-stage.pre-hsts.2026-07-30-2038.conf` — перед HSTS.

**Что проверить через сутки-двое:** [followup-checks.md](followup-checks.md).

## Task

Привести в порядок HSTS и редиректы на прод-сервере
`89.108.113.198` (`cv6064019.novalocal`) для доменов
`niipigrad.ru` и `stage.niipigrad.ru`.

Работа только на сервере в `/etc/nginx/`. К репозиторию проекта
не относится, но результат влияет на выкладку (HSTS «прошивает»
браузер, ошибки в конфиге кладут прод).

## Текущее состояние (аудит 2026-07-30)

### Prod — `/etc/nginx/sites-enabled/niipigrad-prod`

Редиректы работают правильно:
- `http://niipigrad.ru/`     → 301 → `https://niipigrad.ru/`
- `http://www.niipigrad.ru/` → 301 → `https://niipigrad.ru/`
- `https://www.niipigrad.ru/`→ 301 → `https://niipigrad.ru/`
- `https://niipigrad.ru/`    → 200

Сертификат wildcard `*.niipigrad.ru` + apex (`/etc/ssl/prod/`),
покрывает www.

HSTS: `Strict-Transport-Security: max-age=31536000` — только в
основном HTTPS-server-блоке, без `includeSubDomains` и `preload`.

Проблемы:

1. **HSTS не отдаётся на статике и вендор-эндпоинтах.**
   Классическая ловушка nginx: `add_header` **не наследуется** в
   `location`, если там есть свой `add_header`. В prod-конфиге таких
   три:
   - `location ^~ /vendor/livewire/`
   - `location ^~ /vendor/filament/`
   - `location ~* \.(?:css|js|jpg|jpeg|png|gif|svg|ico|webp|woff|woff2|ttf|eot)$`

   На ответах из них HSTS теряется. Для браузера HTML-ответ важнее —
   работает, но некрасиво.

2. **HSTS без `includeSubDomains` и `preload`.** Для HSTS preload
   list нужны обе директивы + `max-age ≥ 31536000` + HTTPS на всех
   поддоменах.

3. **HSTS не отдаётся с 301-редиректов** (www-HTTPS-server и
   http-server). Пользователь, зайдя на `https://www.niipigrad.ru`,
   HSTS получит только после редиректа на canonical.

### Stage — `/etc/nginx/sites-enabled/niipigrad-stage`

- `http://stage.niipigrad.ru/`  → 301 → `https://stage.niipigrad.ru/`
- `https://stage.niipigrad.ru/` → 200

Сертификат Let's Encrypt на `stage.niipigrad.ru` (only), Certbot.

Проблемы:

1. **HSTS не выставлен вовсе.**
2. `www.stage.niipigrad.ru` не описан. DNS-записи нет, сертификат
   не покрывает — по факту недостижим. Не трогаем.

### Прочее

- В `/etc/letsencrypt/live/` остаточный сертификат
  `prod2.niipigrad.ru` — не используется, можно удалить отдельно.

## Key decisions & context

- **stage `max-age` короткий** — `300` секунд. Логика: если TLS
  на stage сломается, клиенты (мы сами) не залипнут на неделю.
  На проде — год.
- **`includeSubDomains` — да.** stage тоже на HTTPS, будущие
  поддомены обязаны быть на HTTPS. Осознанное ограничение.
- **`preload` — нет.** Изначально планировали ставить директиву
  «на будущее», но пересмотрели: попадание в hstspreload-список
  необратимо ~6-12 месяцев (пока новые сборки Chromium/Firefox
  разъедут по пользователям — `max-age` тут не спасает, запись из
  бинарника, а не из заголовка). Директиву добавим только когда
  решим реально подавать и убедимся, что stage-TLS + любые будущие
  поддомены надолго на HTTPS. Держать `preload` в заголовке «просто
  на всякий случай» — риск, что кто-то нечаянно подаст.
- **HSTS инлайном, без snippet.** Обсуждали вариант вынести в
  `/etc/nginx/snippets/hsts-*.conf` и подключать через `include`.
  Отказались: включений всего ~5 на prod и 1 на stage — DRY
  бессмысленно, инлайн-строка лучше читается (открыл конфиг,
  сразу видишь значение), меньше индирекции. Значения:
  - **prod**: `add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;`
  - **stage**: `add_header Strict-Transport-Security "max-age=300" always;`
- **Дублирование в 301-серверах** обязательно: браузер, попавший
  сначала на `https://www.niipigrad.ru`, должен получить HSTS с
  первого ответа.
- **Расширенный scope (добавлено 2026-07-30):** изначально план был
  «только HSTS». При аудите обнаружили, что prod принимает TLSv1/1.1
  (уязвимости BEAST/POODLE-TLS) — default из `/etc/nginx/nginx.conf`.
  Раз всё равно правим prod-конфиг, включаем в этот же заход:
  - **TLS 1.2/1.3 only** + Mozilla intermediate ciphers на server-level
    (в обоих HTTPS-server-блоках — main и www-301).
  - **HTTP/2** — заменяем `listen 443 ssl` → `listen 443 ssl http2`.
  - **Порядок regex-локаций** — `~ /\.(?!well-known).*` (dotfiles deny)
    переносим **перед** `~* \.(?:css|js|...)$` (статика). Иначе файлы
    вида `.hidden.js` или `.env.js` попадали бы в статику раньше блока
    deny (nginx для regex использует first-match-wins).
  Всё это только для prod. Stage не трогаем: TLS-настройки там уже
  нормальные через Certbot-include, HTTP/2 и regex-order — можно
  отложить (Certbot-managed листены рискованно править).
- **НЕ трогаем в этом заходе:** security-headers (X-Frame-Options,
  X-Content-Type-Options, Referrer-Policy, CSP) — требуют тестирования
  с Filament; OCSP stapling — отдельный вопрос настройки resolver;
  глобальный `/etc/nginx/nginx.conf` cleanup — может задеть другие
  сайты.

## Order of work

Все шаги выполняются по SSH на проде. После каждого — обязательно
`nginx -t` перед `systemctl reload nginx`. Откат — `git`-подобной
машинерии нет, поэтому перед правкой копируем оригиналы:

```
cp /etc/nginx/sites-enabled/niipigrad-prod  /root/backup/niipigrad-prod.$(date +%F).conf
cp /etc/nginx/sites-enabled/niipigrad-stage /root/backup/niipigrad-stage.$(date +%F).conf
```

- [x] 1. Забэкапить оба конфига в `/root/backup/`.
      Сделано 2026-07-30: `niipigrad-{prod,stage}.2026-07-30.conf`.
- [x] 2. Prod-конфиг залит и активирован 2026-07-30 20:33.
      Pre-deploy backup: `/root/backup/niipigrad-prod.pre-deploy.2026-07-30-2033.conf`.
      Итоговый конфиг = HSTS в 5 местах + security-headers (nosniff,
      X-Frame-Options SAMEORIGIN, Referrer-Policy) в тех же 5 + `/storage/`
      nested dotfile-deny + TLS 1.2/1.3 + Mozilla intermediate ciphers +
      HTTP/2 + `= /index.php` (остальные .php → 404) + `$realpath_root`
      для OPcache-friendly деплоя.
      Дополнительно в stage добавлено `http2` в listen 443 (через sed на
      сервере, файл-reference обновлён) — иначе warning `protocol options
      redefined` при reload.
- [x] 3. Stage HSTS (`max-age=300`) залит 2026-07-30 20:38.
      Pre-deploy backup: `/root/backup/niipigrad-stage.pre-hsts.2026-07-30-2038.conf`.
      Верификация: `https://stage.niipigrad.ru/` отдаёт HTTP/2 200 +
      `strict-transport-security: max-age=300`. На статике/vendor HSTS
      не дублируем — осознанное решение из-за короткого max-age.
- [x] 4. `nginx -t` — чисто. `systemctl reload nginx` — успех, warning'ов нет.
- [x] 5. Верификация curl'ом — все ответы HTTP/2, HSTS + 3 security-headers
      на всех HTTPS (включая 403 из nested `/storage/.dotfile` — доказано,
      что add_header корректно наследуется в nested location).
      TLS 1.2 работает, TLS 1.1 отклоняется (`no protocols available`).
      `/wp-admin.php` → 404 (PHP-restriction активна).
- [ ] 6. Через сутки-двое, если всё чисто:
      - Обсудить подъём stage `max-age` до боевого. При подъёме нужно
        будет добавить HSTS-дубли в `/vendor/livewire/`, `/vendor/filament/`
        и статику stage-конфига (см. решение из «Key decisions & context»).
      - Подача prod в hstspreload.org — только после отдельного решения
        (см. выше про `preload`; сейчас директивы нет).

## Итоговые конфиги

Готовые файлы лежат рядом с этим README и заливаются на сервер
как есть (одноимённые файлы в `/etc/nginx/sites-enabled/`):

- [niipigrad-prod.conf](niipigrad-prod.conf) — canonical HTTPS +
  www-HTTPS-301 (с HSTS) + http-80-301 (без HSTS, по RFC 6797).
  HSTS дублирован в три location'а с собственным `add_header`
  (`/vendor/livewire/`, `/vendor/filament/`, статика). Плюс:
  TLS 1.2/1.3 + Mozilla intermediate ciphers, HTTP/2 на всех
  `listen ... ssl`, dotfiles-deny перенесён перед статикой.
- [niipigrad-stage.conf](niipigrad-stage.conf) — HTTPS с
  `Strict-Transport-Security "max-age=300"`, Certbot-managed
  http-server не тронут. В `/vendor/livewire/`, `/vendor/filament/`,
  статике HSTS **не** дублируем: max-age короткий, стенд
  тестовый — экономим правки. Если поднимем stage до боевого
  max-age, нужно будет добавить HSTS и в эти три location'а
  (отметить в шаге 6).

## Verification

С сервера (снаружи — те же URL без `--resolve`):

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
    curl -skI \
      --resolve niipigrad.ru:80:127.0.0.1 \
      --resolve niipigrad.ru:443:127.0.0.1 \
      --resolve www.niipigrad.ru:80:127.0.0.1 \
      --resolve www.niipigrad.ru:443:127.0.0.1 \
      --resolve stage.niipigrad.ru:80:127.0.0.1 \
      --resolve stage.niipigrad.ru:443:127.0.0.1 \
      "$url" | grep -Ei '^(HTTP|Location|Strict-Transport|X-Frame|X-Content|Referrer)'
done
```

Ожидаемо (после раскатки 2026-07-30):
- Все prod-HTTPS-ответы — `HTTP/2`, HSTS + 3 security-headers
  (включая 403 из nested `/storage/.dotfile` — headers наследуются).
- `/wp-admin.php` → `HTTP/2 404` (PHP-restriction).
- HTTP-редиректы — без HSTS (по RFC 6797).
- Stage main — `HTTP/2 200 + strict-transport-security: max-age=300`.

TLS-версии:
```
echo | openssl s_client -connect 127.0.0.1:443 -servername niipigrad.ru -tls1_2 2>/dev/null | grep Protocol
echo | openssl s_client -connect 127.0.0.1:443 -servername niipigrad.ru -tls1_1 2>&1 | grep -E "alert|no protocols"
```
Ожидаемо: TLS 1.2 — Protocol : TLSv1.2. TLS 1.1 — `no protocols available`.

## Boundaries

Изначальные — с учётом расширения scope см. «Итог раскатки» наверху.

- **Не** трогали: сертификаты (только читали SAN для проверки),
  permissions, PHP-fpm-пул, ssl_stapling, CSP-заголовок.
- **~~Не трогали `nginx.conf` cleanup~~** — сделано 2026-07-30
  (см. followup-checks.md пункт D).
- **Не** подавали домен в hstspreload.org (директиву `preload` убрали
  из заголовка сознательно — необратимо ~6-12 месяцев).
- **~~Не удаляли `prod2.niipigrad.ru`~~** — удалён 2026-07-30 через
  `certbot delete`, бэкап в `/root/backup/prod2-letsencrypt.2026-07-30.tar.gz`.
- **Не** конфигурировали `www.stage.niipigrad.ru` — нет DNS/сертификата.
- **Не** трогали stage: security headers, PHP-restriction, `/storage/`,
  `$realpath_root`, regex-порядок, gzip_types (только HSTS + http2).

## References

- Аудит и обсуждение — сессия 2026-07-30.
- Конфиги на сервере: `/etc/nginx/sites-enabled/niipigrad-{prod,stage}`.
- Сертификаты: `/etc/ssl/prod/_.niipigrad.ru.*` (wildcard, prod),
  `/etc/letsencrypt/live/stage.niipigrad.ru/` (Certbot, stage).
