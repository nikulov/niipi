# Динамический sitemap.xml

## Задача

Отдавать `sitemap.xml` из БД одним инвок-контроллером, без файла на диске и без
крона. Карта строится в памяти, кешируется в Valkey (тег `sitemap`), сбрасывается
через `saved`/`deleted` на контент-моделях. `robots.txt` объявляет карту.

Промт-донор — из другого проекта, адаптирован под наш стек в чате 2026-07-29.

## Ключевые решения и контекст

- **Модели в карте:** `Page`, `Post`, `Project`. Категории/формы не публикуются
  на своих роутах — не индексируем.
- **Route names уже есть** в `routes/web.php`: `home`, `page.index`, `news.show`,
  `projects.show`.
- **Home-кейс:** `Page` со `slug='home'` отдаётся по `route('home')`, а не
  `route('page.index','home')`. Совпадает с логикой
  `ContentController::normalizePageSlug`.
- **Password-фильтр из промта — не нужен.** Ни одна из наших контент-моделей не
  имеет колонки `password`.
- **`scopePublished` уже есть** у Page/Post/Project (`status = Published AND
published_at <= now()`).
- **Cache store** — Valkey (Redis-совместимый). `CACHE_STORE=redis`, теги
  поддерживаются.
- **`booted()` уже есть у Post и Project** (флашит `['news','categories']` /
  `['projects','categories']`). Нельзя перезаписать — расширяем `cacheTags()`
  дополнительной группой `['sitemap']`. У `Page` `booted()` пока нет —
  добавляем с нуля.

## Закрытые решения

- **Тег кэша = `sitemap`.** Отдельный узко-предметный тег. Не пересекается с
  существующими `['news','categories']` / `['projects','categories']`. Совпадает с
  проектной манерой именовать теги узко.
- **robots.txt = вариант A (статика).** Дописываем строку
  `Sitemap: https://niipigrad.ru/sitemap.xml` в существующий `public/robots.txt`.
- **Прод-домен:** `niipigrad.ru`.

## Order of work

1. **Контроллер + view.**
    - `app/Http/Controllers/SitemapController.php` (`__invoke`, приватный
      `urls()`, константа `ENTITIES = [Post => 'news.show', Project => 'projects.show']`).
    - `resources/views/sitemap.blade.php` — `<urlset>` c `<loc>` + `<lastmod>`
      (через `updated_at?->toAtomString()`).
2. **Роут.** `Route::get('/sitemap.xml', SitemapController::class)->name('sitemap')`
   в `routes/web.php` **выше** `Route::get('/{slug}', …)` — иначе catch-all
   заберёт запрос (regex `^(?!admin|api|login|register).+` не исключает
   `sitemap.xml`).
3. **Инвалидация — Page.** Добавить в `app/Models/Page.php` метод `booted()`
   c `saved`/`deleted` → `cache()->tags(['sitemap'])->flush()`.
4. **Инвалидация — Post и Project.** В существующем `cacheTags()` добавить
   вторую группу `['sitemap']` — существующий цикл во `booted()` сам её сфлашит.
5. **robots.txt.** Дописать в существующий `public/robots.txt`:
   `Sitemap: https://niipigrad.ru/sitemap.xml`.
6. **Обновить `.ai/`.**
    - `file-map.md` — новый контроллер + view (+ robots-контроллер, если B).
    - `architecture.md` — маршрут `/sitemap.xml` (+ `/robots.txt`, если B).
    - `patterns/cache-flush-on-save.md` — упомянуть общий тег `sitemap` для
      публично-индексируемых моделей.

## Файлы

- **NEW** `app/Http/Controllers/SitemapController.php`
- **NEW** `resources/views/sitemap.blade.php`
- **EDIT** `routes/web.php`
- **EDIT** `app/Models/Page.php` (добавить `booted()`)
- **EDIT** `app/Models/Post.php` (`cacheTags()`)
- **EDIT** `app/Models/Project.php` (`cacheTags()`)
- **EDIT** `public/robots.txt` — добавить строку `Sitemap:` с прод-доменом

## Boundaries — что НЕ входит

- Sitemap-index / разбиение (>50k URL).
- `<image:image>` карта.
- `<xhtml:link rel="alternate" hreflang="…">` — до появления мультиязычия.
- `<priority>` / `<changefreq>` — Google игнорирует, `<lastmod>` достаточно.

## Gotchas

- Роут-порядок: `/sitemap.xml` строго до `/{slug}`.
- Первая строка `sitemap.blade.php`: `<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>`
  — иначе парсер PHP примет `<?xml` за открытие своего тега.
- Если возникнет мысль использовать `content` вместо `sitemap` — сначала
  прогрепать `cache()->tags` по кодовой базе.

## Проверка

1. `curl -i http://.../sitemap.xml` → `200`,
   `Content-Type: application/xml; charset=UTF-8`, первым идёт XML-заголовок.
2. По одному `<url>` на каждую опубликованную запись из Page/Post/Project.
3. Правка любой опубликованной модели → `<lastmod>` в карте обновляется.
4. Новая опубликованная запись — сразу в карте.
5. `curl -i http://.../robots.txt` содержит `Sitemap:` c актуальным доменом.
6. Прогон `vendor/bin/sail artisan test` — регрессий нет.

## Checklist

- [x] Решения зафиксированы: тег `sitemap`, домен `niipigrad.ru`
- [x] `SitemapController` + view `sitemap.blade.php`
- [x] Роут `/sitemap.xml` (до catch-all)
- [x] `Page::booted()` c флашем `['sitemap']`
- [x] `Post::cacheTags()` — добавить `['sitemap']`
- [x] `Project::cacheTags()` — добавить `['sitemap']`
- [x] robots.txt — добавить `Sitemap:` (прод-домен)
- [x] Обновить `.ai/file-map.md`, `.ai/architecture.md`, `.ai/patterns/cache-flush-on-save.md`
- [ ] `curl` smoke-проверки 1–5 (после деплоя / локального `sail up`)
- [x] `vendor/bin/sail artisan test --compact` — 244 passed
