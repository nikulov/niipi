# План работы

Общий индекс планов. Конкретные направления — в отдельных файлах.

## Active

- [Nginx: HSTS и канонические редиректы](nginx-hsts/README.md) —
  раскатано 2026-07-30: prod получил HSTS + security headers + TLS 1.2/1.3
  + HTTP/2 + PHP-restriction + `/storage/`-защиту + `$realpath_root`;
  stage — HSTS `max-age=300` + HTTP/2. Осталось: followup-проверки
  через сутки-двое ([followup-checks.md](nginx-hsts/followup-checks.md)).

## Wrapping up

_(пусто)_

## Archive

- [Копирование сущности](archived/duplicate-entity.md) — row action
  «Копировать» для Post/Project/Page/Form: суффиксы «(копия N)» /
  `-copy-N`, сброс статуса, клон пивотов и HasMany, `CopyAction` с
  явной авторизацией по `create` ability. Committed 2026-07-30 (`baa1e52`).
- [Динамический sitemap.xml](archived/sitemap.md) — контроллер + view +
  тегированный кэш `sitemap`, флаш на `saved`/`deleted` у Page/Post/Project,
  строка `Sitemap:` в `robots.txt`. Committed 2026-07-29..30
  (`a2bfc9a`, `2840ad7`).
- [Блок «Якорь»](archived/anchor-block.md) — служебный блок контент-билдера:
  пустой `<div id="…">` для хеш-ссылок, уникальность slug через всю форму
  (включая Tabs/Modal), доступен во всех 6 секциях. Committed 2026-07-29 (`16b2c55`).
- [Медиа-менеджер](archived/media-manager.md) — каталог `media_files`, автотрекинг
  usages, Filament-ресурс, пикер во все `FileUpload`. Merged 2026-07-29.

## Планы

- [tests.md](tests.md) — покрытие тестами: инвентаризация, что покрыто, что нет, приоритетный список тестов к написанию + починка 3 упавших.
- [bugs.md](bugs.md) — 14 багов и cleanup-задач из deep-review 2026-07-14
  (P0–P3, с трассами и планами фикса).
- [bugs-checklist.md](bugs-checklist.md) — компактный чек-лист по [bugs.md](bugs.md).

## Соглашения

- Файл на направление. Индекс (этот файл) держим коротким.
- В каждом плане: цель → текущее состояние → бэклог задач (по приоритету) → definition of done.
- Формулировки в императиве («написать …», «починить …»).
- Когда задача сделана — вычёркиваем в файле плана и, если результат затрагивает будущие сессии, отражаем в `.ai/` по правилам из `~/.claude/CLAUDE.md`.

## Порядок работы

1. Открыть план направления → взять верхнюю невыполненную задачу.
2. Мелкие задачи (1 файл, 1–2 теста) — делать сразу.
3. Крупные (интеграции, миграции, рефакторинг) — сначала обсудить подход.
4. После завершения задачи — прогон затронутых тестов через
   `vendor/bin/sail artisan test --compact --filter=<...>`.
