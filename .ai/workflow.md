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

Для фронтенда (Blade/CSS/JS):

```bash
vendor/bin/sail npm run format
```

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

- Staging: скрипт `deploy-stage.sh` из ветки `staging`. Target:
  `/var/www/niipigrad-stage`.
- Prod: скрипт `deploy-prod.sh` из ветки `main`. Target:
  `/var/www/niipigrad-prod`.
- Стратегия: releases-каталог + симлинк `current`, хранит 5 последних релизов.
- Пост-шаги на сервере: `composer install --no-dev --optimize-autoloader`,
  `migrate --force`, `config:cache`, `route:cache`, `view:cache`,
  `filament:optimize`, `filament:cache-components`, `queue:restart`,
  `systemctl reload php8.4-fpm`.
- Локально эти команды **не запускать** — они предназначены для CI/CD-пути.

## Коммиты

- Ветвление: `staging` — ежедневная разработка, `main` — прод.
- Не добавлять упоминаний Claude/Anthropic в сообщения коммитов.

## Ассеты

- Dev: `vendor/bin/sail npm run dev` (Vite watch).
- Prod-сборка: `vendor/bin/sail npm run build`.
