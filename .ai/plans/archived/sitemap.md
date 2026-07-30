# Динамический sitemap.xml — архив

## Цель

Отдавать `sitemap.xml` из БД одним инвок-контроллером, без файла на диске и
крона. Карта строится в памяти, кешируется в Valkey (тег `sitemap`),
сбрасывается через `saved`/`deleted` на контент-моделях. `robots.txt`
объявляет карту.

## Что сделано

- `app/Http/Controllers/SitemapController.php` — `__invoke` + приватный
  `urls()`; `ENTITIES = [Post => 'news.show', Project => 'projects.show']`.
- `resources/views/sitemap.blade.php` — `<urlset>` с `<loc>` +
  `<lastmod>` (через `updated_at?->toAtomString()`).
- `routes/web.php` — `Route::get('/sitemap.xml', SitemapController::class)`
  строго до catch-all `/{slug}`.
- `Page::booted()` добавлен с флашем тега `sitemap` на `saved`/`deleted`.
- `Post::cacheTags()` и `Project::cacheTags()` — расширены группой
  `['sitemap']`; их существующие `booted()` подхватили флаш.
- `public/robots.txt` — добавлена строка `Sitemap: https://niipigrad.ru/sitemap.xml`.
- `scopePublished` — фильтр «только опубликованное» на карте.
- Home-кейс: `Page` со `slug='home'` → `route('home')`.
- `.ai/file-map.md`, `.ai/architecture.md`,
  `.ai/patterns/cache-flush-on-save.md` — обновлены.
- Тесты: `vendor/bin/sail artisan test --compact` — 244 passed.

## Коммиты

- `a2bfc9a` add dynamic sitemap.xml with tagged cache invalidation
- `2840ad7` cover sitemap: published-only filter, home url, cache flush on save/delete

## Даты

- Начало: 2026-07-29
- Закрытие: 2026-07-30

## Boundaries (не входило)

- Sitemap-index / разбиение (>50k URL).
- `<image:image>`, `<xhtml:link hreflang>`.
- `<priority>` / `<changefreq>`.
