# Nginx: HSTS и канонические редиректы — закрыто 2026-08-10

Работа только на прод-сервере `89.108.113.198` (`cv6064019.novalocal`),
в репозитории кода не отражается. Конфиги-референсы переехали в
[../../infra/nginx/](../../infra/nginx/) — они остаются актуальными,
по ним же правится сервер.

## Цель

Привести в порядок HSTS и редиректы для `niipigrad.ru` и
`stage.niipigrad.ru`. При аудите 30.07 план разросся с «только HSTS» до
полного nginx-хардненинга: обнаружилось, что prod принимает TLSv1/1.1 из
дефолтов `/etc/nginx/nginx.conf`.

## Что раскатано

**Prod (2026-07-30, 20:33–20:38)** — `/etc/nginx/sites-enabled/niipigrad-prod`:

- HSTS `max-age=31536000; includeSubDomains` в 5 местах: main,
  `/vendor/livewire/`, `/vendor/filament/`, `/storage/`, статика,
  www-HTTPS-301. Без `preload` — осознанно.
- Security headers в тех же местах: `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`.
- TLS 1.2/1.3 only + Mozilla intermediate ciphers на server-level.
- HTTP/2 на всех `listen … ssl`.
- PHP restriction: `location = /index.php` — единственная точка exec,
  остальные `.php` → 404.
- `/storage/`: `^~` отменяет regex-исполнение PHP, nested `location ~ /\.`
  закрывает dotfiles.
- `$realpath_root` в `SCRIPT_FILENAME`/`DOCUMENT_ROOT` — OPcache ключится
  на реальный путь релиза, атомарный swap `current/` работает.
- Regex-порядок: dotfiles-deny перед статикой (иначе `.hidden.js` утекал бы).
- `image/svg+xml` в `gzip_types`.

**Stage (2026-07-30)** — HSTS `max-age=300` (короткий, чтобы не залипнуть
на сломанном TLS тестового стенда) + HTTP/2. Security headers,
PHP-restriction, `/storage/`, `$realpath_root`, regex-порядок и gzip_types
на stage **не** трогали: Certbot-managed listen'ы, минимальное касание.

**Catch-all для голого IP (2026-08-05)** — закрыл баг #25. Ни один
`server`-блок на 443 не был `default_server`, дефолтом становился main-блок
prod'а: `https://89.108.113.198/contacts` отдавал приложение, оттуда пришли
20 из 22 пятисоток по #24. Решение — `zz-catch-all`: 80 → 301 на канонический
домен, 443 → `ssl_reject_handshake on`. Редирект на 443 невозможен в
принципе: TLS-рукопожатие идёт до HTTP-запроса, сертификата на IP нет.
Префикс `zz-` — файл должен грузиться после `niipigrad-prod`, где
`ipv6only` задаётся в первом объявлении адреса. Сайт `default` снят: он
владел `listen 80 default_server`, двух таких на порту nginx не соберёт.

Побочно: бот, долбивший форму через голый IP, отваливается на TLS и до
приложения не доходит вовсе.

## Ключевые решения

- **`preload` — нет.** Попадание в hstspreload-список необратимо ~6–12
  месяцев: запись едет в бинарнике браузера, `max-age` не спасает. Держать
  директиву «на всякий случай» — риск, что кто-то нечаянно подаст.
- **`includeSubDomains` — да.** stage уже на HTTPS, будущие поддомены обязаны.
- **HSTS инлайном, без snippet.** ~5 включений на prod и 1 на stage — DRY
  бессмысленно, инлайн-строка читается сразу.
- **Дублирование в 301-серверах обязательно** — браузер, попавший на
  `https://www.niipigrad.ru`, должен получить HSTS с первого ответа.
- **stage `max-age` не поднимаем** — стенд тестовый (решение 30.07).

## Итог followup'а (прогон 2026-08-05)

| # | Проверка | Итог |
| --- | --- | --- |
| 1 | nginx error.log | ✅ ни одного emerg/alert/warn |
| 2 | HTTP-ответы | ✅ совпадают с 30.07 |
| 3 | TLS-версии | ✅ 1.2/1.3 работают, 1.0/1.1 отбиты на prod и stage |
| 4 | SSL Labs | ✅ **A+**, `hasWarnings: false`, `isExceptional: true` |
| 5 | OPcache / `$realpath_root` | ✅ подтверждён выкатками 26.08, см. ниже |
| 6 | Всплеск ошибок | ✅ ни одной сверх известных багов |

Попутно 30.07 сделано: удалён остаточный сертификат `prod2.niipigrad.ru`
(`certbot delete`, бэкап `/root/backup/prod2-letsencrypt.2026-07-30.tar.gz`)
и почищен глобальный `/etc/nginx/nginx.conf`.

## Почему закрыт пункт про OPcache

**Закрыт наблюдением 26.08.2026.** За день прошло три выкатки (релизы
`20260826144614`, `20260826150908`, `20260826171821`), новый код виден сразу,
`opcache_reset` руками не потребовался.

**Важно: обоснование, записанное здесь 10.08, было неверным.** Тогда
считалось, что «на сервере `opcache.validate_timestamps = On`,
`revalidate_freq = 2`, OPcache перечитывает файлы по mtime раз в две
секунды», а `$realpath_root` стоит «за корректность, а не за спасение от
катастрофы». Проверка 26.08 показала обратное — в
`/etc/php/8.4/fpm/php.ini`:

```
opcache.enable=1
opcache.validate_timestamps=0
```

`revalidate_freq` не задан вовсе (при `validate_timestamps=0` он и не
работает), переопределений в `pool.d/` и `conf.d/` нет. То есть OPcache
**никогда** не перечитывает файлы по mtime, и предполагавшейся страховки не
существует.

Реально залипание предотвращают две вещи:

- **`$realpath_root`** в `SCRIPT_FILENAME`/`DOCUMENT_ROOT` — симлинк
  `current/` резолвится в реальный путь релиза, поэтому каждый релиз берёт
  собственные ключи кэша и со старым байткодом не пересекается. При
  `validate_timestamps=0` это несущая конструкция, а не украшение;
- **reload php-fpm при деплое** — в журнале видно
  `Reloading php8.4-fpm.service` в момент переключения симлинка, он сбрасывает
  кэш целиком.

Практическое следствие: правка файла **внутри уже задеплоенного релиза**
(хотфикс на месте) при `validate_timestamps=0` не подхватится вообще — нужен
reload php-fpm. Для нормального деплоя через новый каталог релиза всё
работает.

Если выкатка всё-таки покажет старую версию — смотреть `phpinfo` на
серверный `SCRIPT_FILENAME` и проверять, дошёл ли reload php-fpm.

## Бэкапы на сервере (`/root/backup/`)

- `niipigrad-prod.2026-07-30.conf` — исходное состояние до плана.
- `niipigrad-prod.pre-deploy.2026-07-30-2033.conf` — перед деплоем.
- `niipigrad-stage.2026-07-30.conf`, `…pre-deploy.2026-07-30-2035.conf`,
  `…pre-hsts.2026-07-30-2038.conf`.
- `default.2026-08-05.conf` — сайт `default` до снятия.
- `sites-enabled.2026-08-05.txt` — состав симлинков до правки.
- `prod2-letsencrypt.2026-07-30.tar.gz`.

Откат: вернуть нужный конфиг из бэкапа, `nginx -t`, `systemctl reload nginx`.

## Остаточный риск

`ssl_reject_handshake` рубит клиентов, не присылающих SNI. Браузеры шлют
все; под удар попадает самописный мониторинг или healthcheck, если он ходит
на голый IP по HTTPS. **С владельцем не сверено** — если что-то такое молча
отвалится, причина здесь.

## Ссылки

- Конфиги-референсы: [infra/nginx/](../../infra/nginx/) — совпадают с
  `/etc/nginx/sites-enabled/niipigrad-{prod,stage}` и `zz-catch-all`
  один-в-один.
- Полный текст плана и followup'а — в истории git,
  `.ai/plans/nginx-hsts/` до коммита-архивации.
- Сертификаты: `/etc/ssl/prod/_.niipigrad.ru.*` (wildcard, prod),
  `/etc/letsencrypt/live/stage.niipigrad.ru/` (Certbot, stage).
