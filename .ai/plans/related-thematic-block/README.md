# Блок «Тематическая подборка»

## Task

Новый блочный тип для Post и Project, показывающий связанный контент
из категорий текущей записи. Полиморфный — один блок, разное поведение
для Post (→ новости) и Project (→ проекты).

## Ключевые решения и контекст

- **Один полиморфный блок**, ключ `related-thematic`. В renderer'e
  ветвимся по `$model instanceof Post|Project` — по образцу
  `CategoryListRenderer`.
- **Категории по умолчанию — родительской записи**: если в JSON блока
  `categoryIds` пуст или отсутствует — читаем `$model->categories()`
  в момент рендера. Никакой синхронизации «при сохранении» не нужно —
  блок всегда актуален, потому что читает `categories()` модели
  напрямую. Кэш и так флашится через `Post::booted()`/`Project::booted()`
  на save/delete.
- **Category-Select в админке фильтруется по типу модели** (опция А):
  редактируем Post → показываем только `Category::posts()`, Project
  → только `Category::projects()`. Тип модели узнаём через
  `$livewire->getRecord()` (для Edit) или по классу ресурса
  `$livewire::getResource()::getModel()` (для Create — на Create
  `getRecord()` = null).
- **Секции:** только `mainSection()` (п.7 ТЗ). В `all()` — регистрируем;
  в `topSection`/`bottomSection`/`tabs`/`modal` — **нет**.
- **Дефолтная вставка** — как у `CategoryList`: append в
  `CreatePost::appendDefaultMainBlock` и `CreateProject::appendDefaultMainBlock`.
  Порядок: [...пользовательские блоки, RelatedThematic, CategoryList].
- **Исключение текущей записи** из выдачи — обязательно. Расширяем
  `NewsQuery::list()` и `ProjectsQuery::list()` необязательным
  `?int $excludeId = null`. Существующие вызовы (`NewsBlockRenderer`,
  `ProjectsBlockRenderer`, `Livewire\Components\NewsFull|ProjectsFull`)
  не ломаем.
- **Presenter** переиспользуем: `NewsBlockPresenter` / `ProjectsBlockPresenter`
  (в обоих уже есть `thumbnail`, `title`, `url`, `publishedAt`,
  `categories`).
- **Кнопка «Смотреть все»** — ссылка на `/news?newsCategory={slug}` или
  `/projects?projectsCategory={slug}` — префилтр по **первой категории**
  текущего поста/проекта. Query-param совпадает с `queryString`
  в `Livewire\Components\NewsFull`/`ProjectsFull`
  (`newsCategory` / `projectsCategory`).
- **Стилистика фронта** — 5 квадратных карточек в ряд (2/3/5 на
  mobile/tablet/desktop), `aspect-square`, `object-cover`, оверлей с
  заголовком, ссылка на пост/проект. Кнопка ниже. Без `<x-layout.section-full>`
  (это inner-секция, не full-bleed).
- **В админке блок ничего не рендерит** — обычная Filament-схема
  из `Textarea/TextInput/Select`.

## Order of work

1. [01-query-exclude.md](01-query-exclude.md) — расширить `NewsQuery`/`ProjectsQuery` параметром `excludeId`
2. [02-block-classes.md](02-block-classes.md) — Filament-компонент + Renderer + Blade + локализация
3. [03-register-and-default.md](03-register-and-default.md) — регистрация в двух реестрах + append в Create*
4. [04-tests-and-docs.md](04-tests-and-docs.md) — юнит-тест renderer'а + обновление `.ai/`

## Boundaries

- **Не трогаем** `NewsBlock`/`ProjectsBlock` (это другие блоки — full-bleed с фоном).
- **Не трогаем** `NewsFull`/`ProjectsFull` Livewire-компоненты и `AbstractContentFull`.
- **Не трогаем** политики, роли, кэш-стратегию.
- **Не добавляем** блок в `topSection`/`bottomSection`/`tabs`/`modal`.
- **Не рендерим** блок в админ-форме (никаких live-превью).
- **Не пересоздаём** уже размещённые блоки в существующих Post/Project — только новые записи получают дефолт через `CreatePost`/`CreateProject`.
