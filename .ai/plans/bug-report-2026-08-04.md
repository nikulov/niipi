# Баг-репорт с продакшена — 2026-08-04

Собрано чтением логов прода (`root@89.108.113.198`), только чтение — ничего
не менялось, не чистилось, не перезапускалось.

Нумерация сквозная с [bugs.md](bugs.md) и [archived/bugs.md](archived/bugs.md),
последний занятый номер там — #22, поэтому здесь с **#23**.

По #23, #24, #26 и #27–#30 есть разбор (дописан 2026-08-04, #26 — 2026-08-05).
Остальные пункты — **только симптомы**. Вариантов фикса нет нигде.

**Итог разбора #27–#30:** из четырёх пунктов, заведённых как P1, настоящим
багом остался один — #29 (sitemap), и тот лечится выкаткой. #28 —
не ошибка кода, а незакрытый хвост миграции с WordPress. #27 и #30 сняты
как не-баги и перенесены в раздел «Разобрано — не баги приложения».

## Что просмотрено

| Источник | Путь | Покрытый период |
| --- | --- | --- |
| Laravel | `shared/storage/logs/laravel-*.log` | 17 файлов, 2026-06-01 … 2026-08-04 |
| Worker | `shared/storage/logs/worker.log` | 2026-07-16 … 2026-08-03 |
| Nginx error | `/var/log/nginx/error.log*` | 14 суток (с ротированными) |
| Nginx access | `/var/log/nginx/access.log{,.1}` | 2026-08-03 … 2026-08-04 |
| PHP-FPM | `/var/log/php8.4-fpm.log` | с 2026-08-02 |
| Очередь | таблица `failed_jobs` | вся история |

**Всего в Laravel-логах: 195 записей `production.ERROR`.** PHP-FPM чистый
(только NOTICE о релоадах). Worker чистый — все `SendFormSubmissionEmails`
завершились DONE.

**Важный контекст:** прод сидит на релизе `20260715142417` (симлинк `current`,
15.07). Всё, что смёржено в `staging` после этой даты, на прод не выкачено —
часть пунктов ниже этим и объясняется.

## Легенда

- **P0** — активный баг, ломает пользовательский поток.
- **P1** — активный, но узкий сценарий либо фоновый эффект.
- **P2** — админка / редкий трафик / историческое.
- **Разобрано** — заводилось как баг, при проверке им не оказалось. Номер
  сохраняется, чтобы не разъезжались ссылки.

---

## P0 — ломает пользовательский поток

### 23. Публичная форма падает 500 при отправке с файлом

**Симптом:** `League\Flysystem\UnableToRetrieveMetadata: Unable to retrieve
the file_size for file at location: livewire-tmp/livewire-tmp.`

**Трасса:** `app/Actions/Forms/SubmitFormAction.php:53` (`Validator->validate()`)
← `app/Livewire/Forms/PublicForm.php:85`.

**Частота:** 14 раз. 25.06 — 12 раз, **04.08 — 2 раза (13:34:01, 13:34:26)**.

**Живой сценарий 04.08:** реальный посетитель (`89.22.52.119`, Chrome 150,
HTTP/2) на `https://niipigrad.ru/contacts`. Файл долетел — два
`POST /livewire/upload-file` вернули 200. Следующий `POST /livewire/update`
(отправка формы) — 500. Через 25 секунд повторил — снова 500. Заявка не ушла.

#### Механизм

Полная трасса (префикс релиза убран):

```
#4  livewire/.../TemporaryUploadedFile.php(52): FilesystemAdapter->size()
#5  .../ValidatesAttributes.php(2774):          TemporaryUploadedFile->getSize()
#6  .../ValidatesAttributes.php(1673):          Validator->getSize()
#7  .../Validator.php(686):                     Validator->validateMax()
#11 app/Actions/Forms/SubmitFormAction.php(53): Validator->validate()
#12 app/Livewire/Forms/PublicForm.php(85):      SubmitFormAction->handle()
```

Ключ к разбору — путь в сообщении, `livewire-tmp/livewire-tmp`. Он получается
единственным способом. `TemporaryUploadedFile::__construct` считает путь через
`FileUploadConfiguration::path($path, false)`, а тот собирает
`'livewire-tmp' . ($path ? '/' : '') . $path`. Чтобы вышло
`livewire-tmp/livewire-tmp`, аргумент должен быть строкой `livewire-tmp`.

Цепочка замкнутая:

1. На каком-то `_finishUpload` создаётся `TemporaryUploadedFile` с **пустым**
   путём. `path('', false)` → `livewire-tmp` (слеш не приписывается, потому что
   `$path` пустой). Объект уже битый, но размер у него никто не спрашивает.
2. При дегидрации `serializeForLivewireResponse()` возвращает
   `'livewire-file:' . getFilename()`, а `getFilename()` для пути
   `livewire-tmp` даёт `livewire-tmp`. В снапшот уезжает строка
   `livewire-file:livewire-tmp`.
3. На **следующем** запросе гидрация делает `createFromLivewire('livewire-tmp')`
   → путь становится `livewire-tmp/livewire-tmp`.
4. Правило `max:` зовёт `getSize()` → `Storage::disk('local')->size(...)` →
   файла нет → `UnableToRetrieveMetadata`.

Ломается **не тот запрос, который испортил состояние**: порча происходит на
загрузке, взрыв — на следующей валидации.

#### Почему это 500, а не ошибка валидации

Два места без страховки:

- `Illuminate\Filesystem\FilesystemAdapter::size()` — единственный метод без
  `try/catch`. Соседние `mimeType()`, `move()`, `checksum()` ловят
  `UnableToRetrieveMetadata` и уважают `'throw' => false`, а у диска `local` в
  `config/filesystems.php:37` стоит именно `false`. `size()` пробрасывает
  исключение всегда.
- `app/Actions/Forms/SubmitFormAction.php:50-55` — `validate()` вызывается
  голым. `try/catch` в экшене есть, но обёрнут только вокруг `DB::transaction`
  (строки 61–84), то есть уже после валидации.

#### Что подтверждено логами

- **Все 14 срабатываний — с одним и тем же путём** `livewire-tmp/livewire-tmp`.
  Это всегда случай пустого пути, других вариантов не было ни разу.
- 25.06, 15:41–16:40 — **12 срабатываний подряд с `"userId":5`**. Залогиненный
  пользователь бился об это час. Сегодняшние два — без `userId`, обычный
  посетитель.
- Ни одной ошибки `Unable to retrieve the mime_type` и ни одного
  `must be of type string, bool returned` за всю историю логов. Значит битое
  значение **не доходит до пофайловых правил** `uploads.file.*` (там первым
  сработал бы `mimetypes`), а падает на контейнерном `uploads.file` с `max:5`:
  `Validator::getSize()` в ветке `$value instanceof File` зовёт `getSize()`.
- Ретрай в 13:34:26 дал байт-в-байт тот же ответ (6665). Испорченное значение
  живёт в снапшоте — пользователь не выберется, пока не перезагрузит страницу.

#### Что осталось недоказанным

Почему второй `upload-file` вернул 200, ничего не сохранив:

```
13:33:45 POST /livewire/upload-file 200 169   ← сохранился 1 файл (805 КБ, PDF)
13:33:53 POST /livewire/upload-file 200 34    ← в livewire-tmp/ по-прежнему 1 файл
```

Ответ `{"paths":["\/<имя>"]}` для первой загрузки — ровно 169 байт, сходится
посимвольно (значит gzip не мешает считать размеры). Минимально возможное
сгенерированное имя — 37 символов (`str()->random(30)` + `-meta` + `-` +
`.ext`), в 34 байта реальный сохранённый путь не влезает физически.

Тело запроса nginx не пишет, поэтому что именно браузер отправил в
`_finishUpload`, из логов не достать. Лимиты сервера ни при чём — проверено:
`upload_max_filesize 64M`, `post_max_size 256M`, `client_max_body_size 256M`,
ни одного 413 за двое суток. Закрывается только локальным воспроизведением на
той же конфигурации поля: `multiple: true, max_files: 5, max_size_kb: 5120,
accept_mimes: pdf/jpeg/png` — одинаковая у всех 13 форм в проде.

---

### 24. `Undefined array key "successMessage"` при рендере публичной формы

**Симптом:** `Undefined array key "successMessage" (View:
resources/views/livewire/forms/public-form.blade.php)` → HTTP 500 на
`POST /livewire/update`.

**Место:** `resources/views/livewire/forms/public-form.blade.php:55` —
`{!! $viewData['successMessage'] !!}` читается без guard, тогда как соседние
обращения к `$viewData['fields']` идут через `?? []`.

**Частота:** 28 раз. 14.06 — 8 раз, **04.08 — 20 раз за две секунды
(05:56:02–05:56:03)**.

**Источник всплеска 04.08:** один IP `45.225.135.213`, 20 идентичных
`POST /livewire/update`, referer — `https://89.108.113.198/contacts`
(см. #25). Rate-limiter в `SubmitFormAction` (5 попыток / 300 с) на проде
присутствует, но все 20 запросов дошли до рендера и вернули 500.

**Статус:** починен 2026-08-05. `#[Locked]` на `$form`,
`$viewData`, `$submitted`, `$componentKey` + guard `?? ''` в шаблоне + тест
`test_server_owned_properties_reject_client_updates`. План —
[public-form-crashes](public-form-crashes/README.md).

Оговорка: пятисотки от бота это не убирает —
`CannotUpdateLockedPropertyException` тоже даёт 500. Меняется характер отказа
(отбой до рендера, внятная запись в логе). Счётчик уронит закрытие #25.

#### Это падение рендера, а не отправки

В трассе **нет ни одного кадра из `app/`** — только `HandleComponents->update()`
→ `render()` → `ExtendedCompilerEngine`. `submit()` не вызывался,
`SubmitFormAction` не вызывался, до rate-limiter'а (5 попыток / 300 с) дело не
дошло. Поэтому 20 запросов за две секунды и прошли: лимит стоит внутри экшена,
а крэш случается раньше.

#### Почему падает именно строка 55

В `public-form.blade.php` четыре обращения к `$viewData`, защищены три:

```blade
12: @if (! empty($viewData['title']) ?? null)
22: @foreach ($viewData['fields'] ?? [] as $field)
29: label="{{ $viewData['submitLabel'] ?? __('panel.send') }}"
55: {!! $viewData['successMessage'] !!}        ← без guard
```

Строка 55 сидит внутри `<template x-teleport="body">`. Alpine прячет блок на
клиенте, но сервер рендерит его **всегда**, при любом состоянии компонента —
так что отсутствие ключа роняет любой рендер, а не только успешную отправку.

Презентер ключ ставит безусловно (`PublicFormPresenter:18`,
`(string) ($form->success_message ?? '')`), так что источник — не он.

#### Откуда пропадает ключ

`$viewData` — публичное свойство `PublicForm:17` **без `#[Locked]`**. В проекте
`#[Locked]` не используется нигде (`grep` по `app/` пуст). А Livewire применяет
клиентскую карту `updates` к любому незалоченному публичному свойству:
`HandleComponents::updateProperties()` (строка 308) гоняет `updateProperty()` по
всем присланным путям, и единственный барьер — `BaseLocked::update()`, который
кидает `CannotUpdateLockedPropertyException`. Чексумма покрывает снапшот, но не
`updates` — они накладываются после гидрации.

То есть клиент может прислать `updates: {"viewData": []}` и получить 500. Как и
`submitted`, `componentKey`, `form` — они тоже открыты на запись.

Косвенное подтверждение, что по сайту этим кто-то возит целенаправленно:
**пункт #32 ниже — это ровно `CannotUpdateLockedPropertyException`** на
филаментовских компонентах, 13 раз. Filament свои свойства залочил и поэтому
отдал понятную ошибку вместо 500. `PublicForm` не залочил ничего.

Все 20 сегодняшних пятисоток пришли с referer `https://89.108.113.198/contacts`
— через голый IP (#25), мимо канонического хоста.

#### Побочный момент

Строка 55 — `{!! !!}`, без экранирования. Для админского `success_message` это
осознанно, но значение при этом клиентозаписываемое:
`updates: {"viewData.successMessage": "<img onerror=…>"}` отрендерит
произвольный HTML. Только в ответе самому отправителю, не сохраняется и другим
не отдаётся — то есть self-XSS, серьёзность низкая. Но поверхность та же самая,
что и у падения.

---

## Общее у #23 и #24

Оба про то, что состояние Livewire-компонента принимается на веру: в #23
доверяем пути файла, приехавшему из снапшота, в #24 — содержимому `viewData`.
И оба превращаются в 500 вместо внятной ошибки, потому что на пути наружу нет
ни одного `catch`.

Ещё одна общая черта: `normalizeUploads()` (`PublicForm:100`) перебирает
`$this->viewData['fields'] ?? []`. Если `viewData` пустой — нормализация тихо
становится no-op, и в валидатор уезжает ненормализованный `uploads`. То есть
#24 умеет подпирать #23.

---

## P1 — активные, узкий сценарий или фон

### 25. Сайт отдаётся по голому IP, HTTP не редиректится на HTTPS

**Проверено запросом с сервера:**

```
https://89.108.113.198/contacts  -> 200
http://89.108.113.198/           -> 200   (без редиректа на https)
```

По домену канонизация работает. На 443 ни один `server`-блок не помечен
`default_server`, поэтому дефолтом становился первый загруженный блок с
`listen 443` — main-блок `niipigrad-prod`, то есть по IP отдавалось само
приложение мимо HSTS и канонических редиректов. На 80-м `default_server` был
(сайт `default`), и он отдавал заглушку `index.nginx-debian.html`, а не
приложение.

**Следствие:** 27 запросов за двое суток с IP-referer; **все 20 пятисоток
из #24 пришли именно оттуда** — на домен по 500-м приходится 2 из 22.

**Статус: закрыт 2026-08-05.** Добавлен catch-all
`/etc/nginx/sites-available/zz-catch-all`: 80 → 301 на канонический домен,
443 → `ssl_reject_handshake on`. Симлинк сайта `default` снят. Конфиг,
обоснование и проверки — [nginx-hsts](nginx-hsts/README.md), раздел
«Catch-all для голого IP».

Побочный эффект: бот из #24 теперь отваливается на TLS и до приложения не
доходит вовсе — те 20 пятисоток пропадут физически, а не переедут на домен.

---

### 26. `file_get_contents` по абсолютному URL внутри Blade-компонента

**Симптом:** `file_get_contents(): php_network_getaddresses: getaddrinfo for
niipigrad.ru failed: Temporary failure in name resolution`.

**Место:** `resources/views/components/other/social-icon.blade.php` —
`{!! file_get_contents(public_asset($iconUrl)) !!}`. `public_asset()` отдаёт
абсолютный URL, то есть на каждый рендер иконки сервер ходит HTTP-запросом
сам к себе через DNS.

**Частота:** 10 раз (02.07 — 8, 09.07 — 1, 14.07 — 1) — каждый раз, когда
резолвер моргнул.

**Статус:** исправлен 2026-08-05. Появился хелпер `inline_svg()` в
`app/helpers.php`: резолвит путь по `resource_path()`, потом по диску
`public`, читает файл локально, при промахе отдаёт `''`. Компонент печатает
результат, при отсутствии файла остаётся пустая ссылка вместо 500.

Что учтено сверх исходного симптома:

- **Абсолютные URL.** Первым делом `str_starts_with($path, 'http')` — путь,
  похожий на URL, отбивается сразу. Это прямой запрет на исходный баг: по
  сети хелпер не ходит ни при каких входных данных.
- **Traversal — риск, внесённый самим фиксом, а не найденный в проде.**
  `iconUrl` — обычная строка в JSON-колонке репитера, а не результат
  `FileUpload`; ни `resource_path()`, ни `Storage::path()` не нормализуют
  `..`. В промежуточной версии фикса `inline_svg('../.env')` возвращал
  содержимое `.env` (проверено на контейнере) прямо в `{!! !!}` футера.
  Поймано до коммита, теперь путь с `..` и любое расширение кроме `.svg`
  отбиваются до чтения.

  На проде это не эксплуатировалось: старый код шёл по HTTP, и
  `public_asset('../.env')` давал `https://niipigrad.ru/storage/../.env`,
  который nginx нормализовал в `/.env` и отбивал 403 по deny-правилу (те
  самые срабатывания из раздела «Шум»).
- **Кэш.** Первая версия фикса кэшировала содержимое `rememberForever` по
  `md5($path)`. С `preserveFilenames()` у `FileUpload` перезалитая иконка
  сохраняет путь и никогда не подхватывается, а промах (файла нет в момент
  первого рендера) залипает навсегда — флаша нет нигде. Кэш убран: три
  мелких файла с локального диска дешевле раунд-трипа в Valkey.

**Тесты:** `tests/Unit/InlineSvgHelperTest.php` (7 кейсов, включая traversal
и не-SVG), `tests/Unit/View/Components/Other/SocialIconTest.php` (инлайн и
отсутствующий файл).

---

### 28. Не сделаны редиректы со старых WordPress-URL

Сам по себе 404 здесь корректен — этих маршрутов в приложении нет и быть не
должно. Ошибка в том, что при переезде с WordPress не завели редиректы со
старых адресов на новые, и они продолжают жить в индексе поисковиков и в
чужих ссылках.

**`/cat/news/`** — 202 ответа 404 за двое суток (+203 предшествующих 301 с
`www`). Ответов 200 на `/cat/*` за этот период — ноль. Также прилетают
`/cat/news/page/NN/` и `/cat/news/feed`.

**Это не наша разметка:** grep по `resources/views/` и `routes/` на `cat/`
пуст — сайт нигде на эти URL не ссылается. Заходы идут снаружи, в том числе
с рефереров вида `https://www.niipigrad.ru/cat/news/page/27`, то есть по ним
всё ещё ходят из поисковой выдачи.

Тот же класс — единичные 404 на `/kontakt`, `/contact`, `/contacts.php`,
`/about`, `/about-us`, `/about.html` и на десятки старых slug'ов новостей
вида `/zdanie-torgovo-delovogo-tsentra-dostroeno-v-solnechnogorske`.

**Цена вопроса:** позиции в поиске и внешние ссылки, а не работоспособность
сайта.

---

### 29. `/sitemap.xml` — 404 на проде, `robots.txt` без строки Sitemap

**Проверено:** `https://niipigrad.ru/sitemap.xml` → 404. За двое суток
48 обращений от ботов (21 из них с referer самого сайта).

`robots.txt` на проде целиком:

```
User-agent: *
Disallow:
```

В репозитории `public/robots.txt:9` строка `Sitemap:` есть, маршрут
`routes/web.php:13` есть — но в выкаченном релизе `20260715142417` их нет
(фича закрыта 29–30.07, после даты релиза). См.
[archived/sitemap.md](archived/sitemap.md).

**Чинится выкаткой прода**, правок кода не требует.

---

## Разобрано — не баги приложения

Пункты, заведённые 04.08 как P1 и снятые в тот же день после разбора.
Номера сохранены, чтобы не разъезжались ссылки.

### 27. Legacy-фиды `/feed/` и `/comments/feed/` — внешний бот, а не наша ошибка

| Путь | 404 за 03–04.08 |
| --- | --- |
| `/feed/` | 8215 |
| `/comments/feed/` | 7931 |

Формально это **80 % всех 404** (20 072) и заметно больше, чем весь успешный
трафик (1439 ответов 200 за 04.08). Но источник — не читатели и не
агрегаторы:

- **10 уникальных IP на 16 тысяч запросов.** Шесть из них — из одной подсети
  `85.192.11.0/24` (LLC Digital Network, Москва), по 2357–2918 запросов с
  каждого. Ещё 568 — с `91.107.69.70`.
- **Обратного DNS нет ни у одного.**
- **User-Agent синтетический и ротируется:**
  `Mozilla/5.0 (compatible; I; Linux; x64; en-us) like Gecko`,
  `Opera/5.0 (compatible; Linux; x64; en-US) like Gecko` — таких браузеров
  не существует.
- **Они не запрашивают больше ничего.** Ни одной HTML-страницы, ни одного
  ассета — только эти два URL.
- **Распределение идеально ровное:** ~400 запросов в час круглые сутки, без
  ночного провала.

Бот-ферма, долбящая RSS-эндпоинты старого WordPress-сайта — вероятно парсер,
настроенный ещё во времена WP и с тех пор не выключенный. Laravel отвечает
404 корректно, в `laravel.log` от них **нет ни одной записи**.

**Поправка к цифрам:** каждый опрос логируется дважды — сначала 301
(`www` → канонический хост), потом 404. 16 146 — это не 16 тысяч уникальных
обращений.

**Что реально мешает:** статистика 404 замусорена настолько, что настоящие
битые ссылки в ней не разглядеть. Плюс паразитная нагрузка ~7 запросов в
минуту круглосуточно. Багом сайта не является.

---

### 30. `favicon.ico` нулевого размера — дефолт скелета Laravel

`current/public/favicon.ico` — 0 байт, отдаётся с кодом 200.

Это **штатный файл из скелета Laravel**, он таким и приезжает из коробки:
в git он нулевой с первого коммита (`d91cd9d`). Настоящая иконка сайта лежит
в `public/images/favicon/favicon.ico` (152 КБ) и подключена в
`resources/views/layout/base.blade.php:14` вместе с svg/png-вариантами. В
браузере иконка есть.

Единственное следствие: кто запрашивает `/favicon.ico` по конвенции (боты,
читалки, старые закладки), получает пустой файл. Косметика.

---

## P2 — админка, редкий трафик, историческое

### 31. Filament Notifications: TypeError при гидрации

`Filament\Notifications\Collection::{closure:...fromLivewire():32}():
Argument #1 ($notification) must be of type array, int given`

**Частота: 69 раз** — самая массовая ошибка за всю историю логов.
01.06 (18), 03.06 (8), 05.06 (3), 06.06 (3), 12.06 (12), 14.06 (1),
18.06 (2), 19.06 (2), 10.07 (1), 12.07 (19). После релиза от 15.07 не
повторялась.

---

### 32. `Cannot update locked property`

Свойства: `discoveredSchemaNames` (5), `userUndertakingMultiFactorAuthentication` (4),
`areSchemaStateUpdateHooksDisabledForTesting` (4).

**Частота:** 13 раз — 01.06 (6), 14.06 (1), 10.07 (6).

---

### 33. `Cannot assign array to property Notifications::$isFilamentNotificationsComponent of type bool`

**Частота:** 4 раза — 01.06 (2), 10.07 (2).

---

### 34. Livewire: `corrupt data when trying to hydrate a component`

`CorruptComponentPayloadException` в `Checksum.php:18`.

**Частота:** 13 раз — 12.06 (12), 29.06 (1).

---

### 35. `Unable to find component` — остатки Jetstream/Fortify

Отсутствующие компоненты: `profile.update-profile-information-form`,
`profile.update-password-form`, `profile.two-factor-authentication-form`,
`profile.logout-other-browser-sessions-form`, `profile.delete-user-form`,
`pages.auth.register`, `pages.auth.login`, `filament.pages.auth.register`,
`filament.pages.auth.login`, `auth.register`, `auth.login`.

**Частота:** 22 раза, все 11.06 — по 2 на каждый компонент.

---

### 36. `An action tried to resolve without a name`

View: `vendor/filament/actions/resources/views/components/modals.blade.php`.

**Частота:** 3 раза — 01.06 (2), 10.07 (1).

---

### 37. Вызовы Livewire v2 API на компонентах v3

`Unable to call component method. Public method [emit] not found on component`
и то же для `[dispatchBrowserEvent]`.

**Частота:** 2 раза, обе 10.07.

---

### 38. Блок `form` ссылается на удалённую форму

`Render failed {"section":null,"type":"form","model":"page:1035",
"error":"No query results for model [App\\Models\\Form]."}`

**Частота:** 1 раз, 05.06. Страница `page:1035` содержит блок формы,
указывающий на несуществующий `Form`.

---

### 39. `Handler for event system does not exist`

**Частота:** 1 раз, 10.07.

---

### 40. Восемь потерянных писем по заявкам в `failed_jobs`

| Когда | Исключение |
| --- | --- |
| 21.05, 22.05, 25.05 ×5 | `TimeoutExceededException: App\Jobs\SendFormSubmissionEmails has timed out` (7 шт.) |
| 03.03 13:00 | `TransportException: Connection could not be established with host "ssl://mail.niipigrad.ru:465"` |

Таблица `jobs` пуста, новых падений с 25.05 нет — но эти 8 записей висят
необработанными, письма по соответствующим заявкам не ушли.

---

## Шум — не баги

Зафиксировано, чтобы в следующий раз не разбирать заново.

- **4521 `access forbidden by rule`** в nginx error.log за 14 суток — сканеры
  ботов по `/.env` (183), `/.git/config` (104), `/.env.production`,
  `/.aws/credentials` и ещё ~40 вариантов. Правила из
  [nginx-hsts](nginx-hsts/README.md) отрабатывают штатно, это и есть
  ожидаемое поведение.
- **918 из 20 072 ошибок 404** — сканирование `wp-*`, `.env`, `.git`,
  `admin`, `xmlrpc`, Exchange-эндпоинтов (`/owa/`, `/ews/exchange.asmx`),
  подброс `*.php`-шеллов. Ещё 16 146 из тех же 20 072 — бот-ферма на
  legacy-фидах, разобрана в #27. На всё осмысленное остаётся ~3000.
- **32 `directory index is forbidden`**, **8 `SSL_read() failed`**,
  **2 `recv() failed`** — фоновая ерунда за 14 суток, тренда нет.
- В access.log попадают строки с мусорным статусом (`166`, `"-"`, `SELECT`) —
  битые запросы от сканеров, а не ответы приложения.
