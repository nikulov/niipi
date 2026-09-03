# Рабочий процесс

## Локальный запуск через Sail

Все PHP/Artisan/Composer/Node команды — через Sail.

```bash
# Первичный старт
composer install
vendor/bin/sail up -d
vendor/bin/sail artisan migrate
vendor/bin/sail npm install
vendor/bin/sail npm run dev
```

## Артизан-команды

```bash
vendor/bin/sail artisan make:migration create_foo_table --no-interaction
vendor/bin/sail artisan migrate
vendor/bin/sail artisan migrate:rollback
vendor/bin/sail artisan filament:upgrade   # запускается автоматически на composer install
vendor/bin/sail artisan tinker --execute 'App\Models\Post::count()'  # только одинарные кавычки
```

## Тесты

```bash
vendor/bin/sail artisan test --compact
vendor/bin/sail artisan test --compact --filter=SomeFeatureTest
```

Тестовая БД поднимается по `DB_DATABASE=testing` (см. `phpunit.xml`).

## Форматтеры

После правки PHP:

```bash
vendor/bin/sail bin pint --dirty
```

`--format` понимает только `txt` (дефолт), `json`, `checkstyle`, `junit`,
`gitlab`. Проверка без правки файлов — `--test`.

Для фронтенда (Blade/CSS/JS) — точечно по изменённым файлам:

```bash
vendor/bin/sail npx prettier --write resources/views/…/file.blade.php
```

`npm run format` (весь `resources/`) без нужды не запускать — подробности
и причина в [conventions.md](conventions.md), раздел «Frontend».

## Сервисы Sail

- MySQL — БД
- Valkey — Redis-совместимый (кэш/сессии/очереди при необходимости)
- Meilisearch — поиск
- Mailpit — перехват писем в dev (доступен через веб-интерфейс порта Mailpit)

## Доступ к серверу

- Prod — `/var/www/niipigrad-prod/current`, stage — `/var/www/niipigrad-stage/current`
  (симлинк на `releases/<timestamp>`).
- Artisan запускать от владельца релиза:
  `sudo -u deploy_niipigrad php artisan …` — под root'ом артефакты в `storage/`
  и `bootstrap/cache/` останутся с чужими правами.
- Очередь на проде — **redis**, не `database`: падшие джобы смотреть через
  `php artisan queue:failed`.
- Любая команда на проде — только по явной просьбе, и только на том стенде,
  который назвали.
- **`.env` стендов живёт в `shared/.env`** и переменными перебивает дефолты из
  `config/*.php`. Если правка конфига «не подействовала» на сервере — сначала
  смотреть туда, а не в код. Отличия прода на 2026-08-26:
  - `LOG_DAILY_DAYS=90` — выставлено явно. Дефолт в `config/logging.php` тоже
    90 (`bb0bd03`), но до правки в `.env` стояло 30 и перебивало его, так что
    релиз с «90 дней» на проде ничего не менял. На stage переменной нет —
    работает дефолт.
  - `MAIL_REPLY_TO_ADDRESS=website@niipi.ru` — на обоих стендах.
  После правки `.env` кеш конфига пересобрать:
  `sudo -u deploy_niipigrad php artisan config:cache` + `systemctl reload php8.4-fpm`,
  иначе значение подхватится только со следующим деплоем.

### Логи и сервисы на проде (проверено 2026-09-03)

- Логи приложения — `current/storage/logs/laravel-YYYY-MM-DD.log`. `storage`
  общий через `shared/`, деплой их не теряет.
- **`LOG_LEVEL=error`.** Всё, что пишется `Log::warning` (отсев мёртвых файлов
  в `PublicForm`, например), в прод-лог не попадает вообще. И наоборот:
  отсутствие файла за день не значит, что логи потёрли ротацией
  (`LOG_DAILY_DAYS=90`) — в тихий день просто нечего писать.
- nginx — `/var/log/nginx/access.log`, `.1`, `.2.gz`… `.14.gz` (две недели),
  рядом `error.log`. Единственный источник по кодам ответа: 419, 499 и коды
  статики в лог приложения не попадают.
- Redis на самом деле **valkey**: `redis-cli` не установлен, звать
  `valkey-cli`. Пароля нет (`requirepass` закомментирован, `REDIS_PASSWORD=`
  пуст) — предупреждение `AUTH failed` в выводе игнорировать, команда всё
  равно выполняется. Сессии в `db0`, кэш в `db1`, `maxmemory` не задан
  (`noeviction`), `evicted_keys:0` — сессии не вытесняются, и `cache:clear`
  на деплое их не трогает.
- Воркер очереди — systemd, а не supervisor:
  `worker-niipigrad-prod.service` (stage — `-stage`),
  `User=deploy_niipigrad`, `queue:work redis --sleep=3 --tries=3
  --max-time=3600`, `Restart=always`. Деплой делает `systemctl restart`.
- В `storage/logs/worker.log` копятся фаталы вида
  `include(.../releases/<старый>/vendor/...): Failed to open stream` — это
  воркеры, чей релиз вычистил `KEEP_RELEASES`; systemd поднимает их заново,
  вреда нет.
- **Планировщика нет.** Пусты crontab'ы `root`, `deploy_niipigrad`,
  `www-data`, `ubuntu`, systemd-таймера под `schedule:run` тоже нет. Сейчас
  это верно: задач в `routes/console.php` нет. Появится первая — cron
  придётся завести, иначе она молча не будет выполняться.

## Деплой

- Staging: `/var/www/niipigrad-stage`, ветка `staging`. Prod:
  `/var/www/niipigrad-prod`, ветка `main`.
- **Боевой скрипт лежит на сервере**, а не в репозитории:
  `/var/www/niipigrad-prod/deploy-prod.sh`, запускать
  `sudo -u deploy_niipigrad /var/www/niipigrad-prod/deploy-prod.sh`. Он сам
  проверяет, что запущен от `deploy_niipigrad`, берёт лок `.deploy.lock`,
  делает `fetch` в `repo.git` и `checkout` в новый `releases/<timestamp>`.
- `deploy-prod.sh` / `deploy-stage.sh` **удалены из репозитория** 2026-08-26:
  лежавшие там копии давно разошлись с боевыми (другие пути —
  `/var/www/niipi-prod`, нет лока, отката и проверки диска) и сбивали с толку.
  Единственный источник правды — файл на сервере, он же
  `/var/www/niipigrad-stage/deploy-stage.sh` для stage.
- Стратегия: releases + симлинк `current`, `KEEP_RELEASES=5`.
- Общие между релизами (симлинки в `shared/`): `.env`, `storage`,
  `bootstrap/cache`.
- Шаги скрипта: `composer install --no-dev --optimize-autoloader` →
  `migrate --force` → `optimize:clear` → `storage:link` → `config:cache` →
  `route:cache` → `view:cache` → переключение `current` → `filament:assets` →
  `filament:cache-components` → рестарт воркера → reload php-fpm и nginx →
  `opcache_reset` → чистка старых релизов.
- Локально эти команды **не запускать**.
- **`.ai/` и `.claude/` в релиз не едут.** Сразу после `checkout` скрипт делает
  `rm -rf "$RELEASE_DIR/.ai" "$RELEASE_DIR/.claude"` — в git они остаются, на
  сервере им не место (2026-08-26, оба стенда). Утечки и до этого не было:
  nginx root — `current/public`, обе папки лежат выше веб-корня и отдавали 403.
  Строка стоит **до** переключения `current`, то есть под `ERR`-trap: если она
  упадёт, деплой честно откатится.
- Разовые шаги к конкретному релизу живут в «Active» в
  [plans/plan.md](plans/plan.md) — перед выкатом заглянуть туда.

### Откат и общий `bootstrap/cache` — как ронялся прод

`trap 'rollback' ERR` возвращает `current` на прошлый релиз и делает
`rm -rf` нового. Беда в том, что `bootstrap/cache` **общий**: `config:cache`
зашивает в него абсолютные пути внутрь текущего релиза. Если откат сносит
каталог, живой релиз продолжает читать кеш с путями в удалённое место и весь
публичный сайт отдаёт 500 (`View [layout.page] not found`,
`Unable to create a directory at .../storage/app/public`). Админка при этом
может отвечать 200 — не считать это признаком живого сайта.

Лечится сбросом общего кеша, сайт поднимается сразу:

```bash
sudo -u deploy_niipigrad rm -f \
  /var/www/niipigrad-prod/shared/bootstrap/cache/{config,routes-v7,services,packages}.php
sudo -u deploy_niipigrad rm -rf /var/www/niipigrad-prod/shared/bootstrap/cache/filament
systemctl reload php8.4-fpm
```

2026-08-26 скрипт пропатчен, чтобы это не повторялось: `trap - ERR` сразу
после `ln -sfn "$RELEASE_DIR" "$BASE/current"` (после переключения релиз живой,
хвостовые шаги не должны его откатывать) и `|| true` на блоке чистки старых
релизов. Бэкап оригинала — `/root/backup-deploy-prod.sh.20260826`.

### Права: чужие файлы ломают деплой

Всё, что создаёт php-fpm, принадлежит `www-data`, и `deploy_niipigrad` это не
удалит и не перезапишет. Дважды подряд деплой падал именно так: чистка старых
релизов спотыкалась о `rm: cannot remove
'./20260525152833/storage/logs/laravel-2026-05-25.log': Permission denied`,
`rm` возвращал ненулевой код и утаскивал за собой успешный релиз через
`ERR`-trap.

- Проверить перед выкатом:
  `find /var/www/niipigrad-prod/releases ! -user deploy_niipigrad | wc -l` — должно быть `0`.
- Та же засада в `shared/storage/app/public/images` (владелец `www-data`,
  права 755): создать там подкаталог от деплой-пользователя нельзя. Делать от
  root с `chown deploy_niipigrad:www-data` и `chmod 2775`.

## Коммиты

- Ветвление: `staging` — ежедневная разработка, `main` — прод.
- Не добавлять упоминаний Claude/Anthropic в сообщения коммитов.

## Ассеты

- Dev: `vendor/bin/sail npm run dev` (Vite watch).
- Prod-сборка: `vendor/bin/sail npm run build`.
