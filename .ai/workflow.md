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
