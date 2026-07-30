# Проверки через сутки-двое после раскатки 2026-07-30

Цель — убедиться, что новый nginx-конфиг стабилен, ничего не сломал
в проде, и решить по остаточным пунктам (подъём stage `max-age`,
уборка старого сертификата).

**Ориентир по срокам:** запустить эти проверки не раньше **2026-08-01**
(сутки после раскатки) и не позже **2026-08-06** (пока свежо в памяти).

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

### 3. TLS-версии на месте

```
echo | openssl s_client -connect niipigrad.ru:443 -servername niipigrad.ru -tls1_2 2>/dev/null | grep Protocol
echo | openssl s_client -connect niipigrad.ru:443 -servername niipigrad.ru -tls1_1 2>&1 | grep -E "alert|no protocols"
```

TLS 1.2 — работает. TLS 1.1 — отклоняется.

### 4. SSL Labs grade (внешний прогон)

Открыть в браузере: https://www.ssllabs.com/ssltest/analyze.html?d=niipigrad.ru&hideResults=on

Ожидаемо после наших правок — **A** или **A+** (было бы **B** из-за
TLS 1.0/1.1, если бы не поправили). Grade **T** или ниже — красный
флаг, срочно разбираться.

### 5. OPcache: релиз реально подхватывается

Проверить после следующего деплоя (или создать тестовый деплой):
- Сделать редакцию в `resources/views/*.blade.php` — что-то заметное.
- Задеплоить через обычный процесс (симлинк `current/` переключается).
- Проверить, что новая версия видна в браузере **без** `opcache_reset`
  или `systemctl reload php8.4-fpm`.

Если новая версия не видна — `$realpath_root` не сработал, разбираться
(проверить `phpinfo` на серверном пути к SCRIPT_FILENAME).

### 6. Никаких жалоб пользователей / клиентов

Проверить:
- Support-каналы (если есть).
- Sentry/Bugsnag/логи Laravel — всплеск ошибок с 2026-07-30 20:33.
- `storage/logs/laravel.log` — что-то новое после этого времени?

Особенно важно: не отвалились ли какие-то `.php` пути. Ожидание —
нет, но если кто-то у нас через костыль вызывал не index.php
(маловероятно, но проверить).

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

Побочно замечены **stale .bak-файлы** в `/etc/nginx/sites-available/`:
- `niipigrad-prod.bak-20260525-{155818,160150,160508}`
Ссылаются на prod2 в закомментированных строках. Не активны
(не в sites-enabled), но захламляют. Если руки дойдут — перенести
в `/root/backup/` или удалить.

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

- Отметить все проверки в этом файле как выполненные.
- Если решили подъём stage `max-age` или чистку `prod2` — сделать
  отдельным заходом и добавить пункты в основной README план.
- Когда всё чисто и остаточных пунктов нет — план целиком закрывается,
  переходит в `plans/plan.md → Archive` (правила из `~/.claude/CLAUDE.md`).
