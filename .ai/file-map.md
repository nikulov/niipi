# Карта файлов

## Корень

- `composer.json`, `package.json` — зависимости
- `compose.yaml` — Sail (mysql, valkey, meilisearch, mailpit)
- `phpunit.xml` — конфиг тестов
- `deploy-stage.sh`, `deploy-prod.sh` — деплой (staging/prod)
- `prettier.config.cjs`, `.prettierignore` — форматирование (blade + tailwind)
- `bootstrap/app.php` — сборка приложения
- `bootstrap/providers.php` — список провайдеров (`AppServiceProvider`, `AdminPanelProvider`)
- `routes/web.php` — публичные роуты
- `routes/console.php` — консольные команды (только `inspire`)
- `vite.config.js` — Vite + `@tailwindcss/vite`

## app/

| Путь                                                                  | Что там                                                                                                                                                      |
| --------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `Models/`                                                             | Eloquent модели (Page, Post, Project, Category, Menu, Footer, GlobalSetting, Form, FormField, FormSubmission, FormSubmissionFile, User)                      |
| `Models/Concerns/HasSectionOptions.php`                               | Механика «блоков-настроек» (например, `bg-for-main-section`)                                                                                                 |
| `Models/Concerns/Duplicatable.php`                                    | Трейт «Копировать»: `duplicate()` через `ModelDuplicator`, хуки `prepareDuplicate` / `copyRelationsTo` / `duplicateTitleColumn` / `duplicateSlugColumn`      |
| `Http/Controllers/ContentController.php`                              | Публичные страницы: `page()`, `post()`, `project()`                                                                                                          |
| `Http/Controllers/SitemapController.php`                              | `__invoke` → XML-карта из Page/Post/Project, кэш тегом `sitemap` (TTL 1ч, backstop)                                                                          |
| `Http/Controllers/MenuController.php`                                 | **Пустой класс**, роутов не имеет, кандидат на удаление                                                                                                      |
| `Livewire/Components/`                                                | `AbstractContentFull` (база), `ProjectsFull`, `NewsFull` — списки с категориями и пагинацией                                                                 |
| `Livewire/Forms/PublicForm.php`                                       | Публичная форма (WithFileUploads + honeypot)                                                                                                                 |
| `Filament/Resources/{Entity}/`                                        | Filament ресурс — `Pages/`, `Schemas/`, `Tables/`, при необходимости `RelationManagers/`                                                                     |
| `Filament/Resources/Forms/RelationManagers/FieldsRelationManager.php` | Поля формы (relation manager)                                                                                                                                |
| `Filament/Resources/Forms/Schemas/FormMailActions.php`                | Предпросмотр и тестовая отправка письма — экшены секций «Письмо администратору / пользователю» в `FormForm`                                                   |
| `Filament/Components/*.php`                                           | Filament Block-компоненты (по одному на тип блока)                                                                                                           |
| `Filament/Components/BgForMainSection.php`                            | Спец-блок для «настроек секции»                                                                                                                              |
| `Filament/Components/BlockRegistry/BlockRegistry.php`                 | Реестр блоков для Filament (`all()`, `topSection()`, `mainSection()`, `bottomSection()`, `tabs()`, `modal()`)                                                |
| `Filament/Forms/Components/CustomRepeater.php`                        | Repeater с нумерованными label'ами                                                                                                                           |
| `Filament/Forms/Components/UrlInput.php`                              | TextInput с префиксом `niipigrad.ru/` и кнопкой открытия                                                                                                     |
| `Filament/Support/RoleAccessResource.php`                             | Trait: `shouldRegisterNavigation()` + `canViewAny()` по `allowedRoles()`                                                                                     |
| `Filament/Support/SeoSync.php`                                        | `copy()` — заливает `title`/`description` в `meta_title`/`meta_description` из `afterStateUpdated` форм Post/Project (см. ниже)                              |
| `Filament/Actions/CopyAction.php`                                     | Row action «Копировать» (икона DocumentDuplicate, `getDefaultName='copy'`, вызывает `$record->duplicate()`)                                                    |
| `Filament/Widgets/SubmissionsStats.php`                               | Dashboard stat-виджет по заявкам                                                                                                                             |
| `Blocks/BlockRenderRegistry.php`                                      | Реестр рендереров (map `type => Renderer`)                                                                                                                   |
| `Blocks/Renderers/`                                                   | Рендер каждого типа блока (см. ниже)                                                                                                                         |
| `Blocks/Contracts/BlockRenderer.php`, `HasBlockSections.php`          | Интерфейсы блочного рендера                                                                                                                                  |
| `Services/ContentRenderer.php`                                        | Диспатч блоков секции; кэш пока закомментирован                                                                                                              |
| `Services/ModelDuplicator.php`                                        | Копирование записи в `DB::transaction`: суффиксы `(копия N)` / `-copy-N`, replicate + patch title/slug, вызов хуков модели                                    |
| `Services/NewsQuery.php`, `ProjectsQuery.php`                         | Публичные выборки (list + paginate)                                                                                                                          |
| `Services/Forms/`                                                     | `FormRulesBuilder`, `FormValidationAttributesBuilder`, `SubmissionCreator`, `SubmissionDataNormalizer`, `SubmissionFilesStorer`, `FormEmailTemplateRenderer` |
| `Services/Forms/FormEmailTemplateRenderer.php`                        | Шаблоны писем: `renderSubject()`, `renderBodyHtml()`/`renderBodyText()` (подстановка плейсхолдеров), `renderLetterHtml()` (боевое письмо в обёртке), `renderPreviewHtml()` (превью и тест — без подстановки) |
| `Actions/Forms/SubmitFormAction.php`                                  | Оркестратор: rate-limit → валидация → нормализация → сохранение → job писем                                                                                  |
| `Presenters/Forms/PublicFormPresenter.php`                            | `viewData` для `PublicForm`                                                                                                                                  |
| `Presenters/Forms/FormSubmissionPresenter.php`                        | Для админки/писем                                                                                                                                            |
| `Presenters/Blocks/`                                                  | `NewsBlockPresenter`, `NewsFullPresenter`, `ProjectsBlockPresenter`, `ProjectsFullPresenter` — DTO карточек                                                  |
| `Enums/`                                                              | `PageStatus`, `PostStatus`, `ProjectStatus`, `CategoryStatus`, `CategoryType`, `FormApplicantType`, `FormSubmissionStatus`, `UserRole`                       |
| `Providers/AppServiceProvider.php`                                    | Observers, `helpers.php`, `Filament\Notifications::alignment`, share `year`, `settings`, footer composer                                                     |
| `Providers/Filament/AdminPanelProvider.php`                           | Панель `admin`, тема, автопоиск ресурсов/страниц/виджетов, hardcoded `navigationGroups` (русские строки)                                                     |
| `Observers/`                                                          | `PageObserver`, `PostObserver`, `ProjectObserver` — авто-`published_at` при переходе в Published                                                             |
| `Policies/BasePolicy.php`                                             | `before()` = Admin bypass; хелперы `isEditor`, `isViewer`, `isEditorOrViewer`                                                                                |
| `Policies/*Policy.php`                                                | По одной на модель, наследуются от `BasePolicy`                                                                                                              |
| `Contracts/HasMeta.php`                                               | `meta(): array` — для страниц с SEO-мета                                                                                                                     |
| `View/Composers/FooterComposer.php`                                   | `includes.footer` → `$footer` из `Footer::cachedData()`                                                                                                      |
| `View/Components/Menu/`                                               | Blade-компоненты `DesktopFooter`, `Top`                                                                                                                      |
| `Mail/`                                                               | `AdminFormSubmissionMail`, `UserFormSubmissionMail`, `TemplatedFormSubmissionMail`                                                                           |
| `Jobs/SendFormSubmissionEmails.php`                                   | Асинхронная отправка писем (tries=5, backoff [60,300,900])                                                                                                   |
| `helpers.php`                                                         | `public_asset()` — Storage disk `public` или прямой URL                                                                                                      |

## Блоки

`app/Blocks/Renderers/`:
Accordion, AccordionLight, Anchor, Button, CardsBlockWithButton,
CardsBlockWithImageTitle, CategoryList, Form, Gallery, ImageFull,
ImageText, ImageTittleFullWidth (sic — с двойной `t`), InfoBlockWithAchievements,
InfoBlockWithButtons, ModalBlock, NewsBlock, NewsFull, ProjectsBlock,
ProjectsFull, RelatedThematic, ShareButton, SliderFullWidth, TabsBlock, TextFull,
Title, YandexMap.

`ShareButton` (`share-button`, «Поделиться + кнопка») — блок `button` плюс
шаринг: слева от кнопки триггер, по клику полоска выезжает вправо **поверх**
кнопки. **Схема в Filament идентична `Button`** — соцсети в админке не
настраиваются: набор (ВК, Telegram, MAX, «копировать ссылку») и иконки
(`components/icon/icon-{vk,telegram,max,link,share}`) зашиты в шаблон.
Адреса собирает Alpine-хелпер `share()` из шаблонов с `{url}` / `{title}`,
подставляя `location.href` и `document.title` — блок шарит ту страницу, на
которой стоит. У MAX веб-диалога шаринга нет — только диплинк в приложение
`https://max.ru/:share?text={url}` (`text` — тело сообщения, поэтому шарим
голую ссылку без заголовка). Состояние — инлайновый `x-data`, в `app.js`
ничего нет. Пока полоска открыта, триггер держит ховер-цвет: в `app.css`
у всех четырёх `.btn-*-bg` есть вложенный `&[aria-expanded='true']` с теми же
цветами, что и `hover:`. `aria-expanded` во всём проекте только у этого
триггера, так что остальных кнопок правило не касается.
`ShareButton::getDefaultBlock($btnUrl, $btnLabel)` — дефолтный блок для
Post/Project (см. [domain.md](domain.md#блочный-контент)); в отличие от
`CategoryList`/`RelatedThematic` метод параметризован, потому что новости и
проекты ведут на разные разделы. `btnUrl` хранится с ведущим слэшем (`/news`),
так как шаблон подставляет его в `href` как есть, а блок стоит на вложенной
странице `/news/{slug}` — относительный `news` (как в дефолте `NewsBlock`)
там бы сломался.

`Title` (`title`, «Заголовок») — кроме схемы держит логику автоподстановки
заголовка записи (только Post/Project, для Page ничего не подставляется):

- `Title::getDefaultBlock()` — дефолтный блок main-секции форм Post/Project
  (`h2`, `position=center`, без `title`). Подключён через
  `Builder::make('main_section')->default(...)`, то есть срабатывает только
  на создании: `default()` применяется при `fill(null)` и существующие записи
  на Edit не трогает.
- `Title::syncRecordTitle(Set, Get, $state, $old)` — вызывается из
  `afterStateUpdated` поля `title` в `PostForm`/`ProjectForm` (там же живёт
  автогенерация slug, оба действия только при `operation === 'create'`).
  Пишет заголовок записи в **первый** блок `title` из `main_section` точечным
  `$set("main_section.{key}.data.title", …)`. Условие — заголовок блока пуст
  **или** равен `$old` (предыдущему значению поля): правки опечаток доезжают,
  переписанный вручную H2 не затирается. `$old` даёт Filament
  (`callAfterStateUpdatedHook`).
- `default()` у Textarea блока — предзаполнение при **ручном** добавлении
  блока: Filament кладёт новый item в state и только потом зовёт `fill(null)`,
  поэтому в `$livewire->data` новый блок уже виден. Подставляем заголовок,
  только если блок `title` во всей форме единственный (рекурсивный обход
  включает вложенные `tabs-block`/`modal-block`).

`RelatedThematic` — полиморфный блок «тематическая подборка»: в Post показывает
связанные новости, в Project — связанные проекты. Категории по умолчанию —
из `$model->categories()`; в `data.categoryIds` можно переопределить. Текущий
Post/Project исключается из выдачи. Только заголовок и сетка карточек —
кнопки «Смотреть все» нет (убрана 2026-08-04; в JSON старых записей мог
остаться ключ `btnLabel`, рендерер его игнорирует). Только в `mainSection`.
Добавляется дефолтом при создании Post/Project (`CreatePost`/`CreateProject`)
перед `CategoryList`.

`ProjectsBlock` — `data.projectIds` закрепляет конкретные проекты: они идут
первыми в порядке массива (мультиселект с `->reorderable()` — порядок задаётся
перетаскиванием), остаток добирается автоподбором с исключением
уже закреплённых, всё вместе режется по `data.limit`. Пустой `projectIds` даёт
чистый автоподбор. Неопубликованные id молча выпадают (`ProjectsQuery::byIds()`).
Ключ количества — `limit`; до 2026-08-05 поле в Filament называлось `quantity` и
рендерером не читалось вовсе, старые записи со `quantity` откатываются на 4 до
первого пересохранения блока.

Filament-компоненты в `app/Filament/Components/` — один-в-один с рендерерами
плюс `BgForMainSection` (спец-блок настройки).

`Anchor` — служебный блок: пустой `<div id="…">` для хеш-ссылок, доступен во
всех секциях (`all/top/main/bottom/tabs/modal`). Уникальность slug проверяется
через всю форму (рекурсивный обход `$livewire->data`), а не только внутри
текущего Builder.

## Автозаполнение полей в формах Post/Project

Всё висит на `afterStateUpdated` полей `title` и `description`
(`PostForm`/`ProjectForm`); `description` ради этого стало `live(onBlur: true)`.

| Что                                                        | Когда             | Защита от ручной правки                                 |
| ---------------------------------------------------------- | ----------------- | ------------------------------------------------------- |
| `slug` ← `title`                                           | только create     | пишем, лишь пока `slug` пуст                            |
| блок `title` в `main_section`                              | только create     | пусто **или** равно `$old` (`Title::syncRecordTitle()`)  |
| `meta_title` ← `title`, `meta_description` ← `description` | create **и** edit | пусто **или** равно `$old` (`SeoSync::copy()`)           |

`$old` — предыдущее значение поля, его отдаёт Filament в
`callAfterStateUpdatedHook()`. Правило «пусто или равно `$old`» означает:
переименовал запись — SEO/заголовок блока догнали; написал свой текст —
он больше не трогается.

## resources/views/

- `layout/` — макеты (в т.ч. `layout.page`)
- `components/` — Blade-компоненты: `buttons/`, `form/`, `icon/`, `layout/`,
  `logo/`, `menu/`, `other/`, `sections/`
- `livewire/components/`, `livewire/forms/` — шаблоны Livewire
- `includes/`, `emails/`, `forms/`, `vendor/`
- `emails/email-template.blade.php` — брендовая обёртка письма: шапка-картинка
  с запечённым логотипом, карточка 600px, футер с контактами и соцсетями.
  Таблицы, стили только инлайн, в `<style>` один `@media`. Все переменные с
  дефолтами. Картинки из `public/images/email/` — **мимо Vite**: хэш в имени
  меняется при сборке, а письмо уходит со старой ссылкой. Подключена к обоим
  письмам по заявкам через `FormEmailTemplateRenderer::renderLetterHtml()`.
  Решения по вёрстке и сборке письма — [plans/archived/email-template.md](plans/archived/email-template.md).
  **Правишь вёрстку письма** — раскомментируй роут `/_preview/email` в
  `routes/web.php` (он живой, просто закомментирован) и смотри шаблон в браузере
  без отправки. Закончил — закомментируй обратно.
- `vendor/mail/` — тема markdown-писем Laravel, через неё в брендовую обёртку
  попадают все системные письма (сброс пароля, подтверждение email, коды MFA).
  `html/layout.blade.php` вместо стандартной таблицы включает
  `emails.email-template`, отдав ему телом `Markdown::parse($slot)` + subcopy,
  завёрнутые в `<div class="letter">`. `html/themes/default.css` вычищен и
  **заскоплен на `.letter`** — Laravel инлайнит его через `CssToInlineStyles` по
  всему документу, и неограниченные селекторы (`p`, `a`, `body *`) переписали бы
  футер шаблона. Строки Laravel переведены в `lang/ru.json`, строки Filament —
  его собственные ru-переводы. Сводка — [plans/archived/system-emails.md](plans/archived/system-emails.md).
- `forms/email-preview.blade.php` — модалка предпросмотра письма в админке,
  рисует его в `<iframe srcdoc>`: письмо это целый документ, вставленный в
  страницу он дрался бы стилями с панелью.
- `sitemap.blade.php` — XML для `/sitemap.xml` (см. `SitemapController`)

## resources/css/

- `app.css` — Tailwind 4 корневой файл (`@theme`, `@source`, `@utility`)
- `filament/` — темы Filament

## database/

- `migrations/` — миграции. Есть базовые `0001_01_01_*` + 30+ доменных
  (`2025_10_*` → `2026_02_*`).
- `factories/UserFactory.php`
- `seeders/DatabaseSeeder.php`, `FooterSeeder.php`, `UserSeeder.php`

## tests/

- `Unit/` — `Actions/`, `Blocks/Renderers/`, `Jobs/`, `Mail/`, `Models/`,
  `Observers/`, `Presenters/Blocks|Forms`, `Providers/`, `Services/`, `Services/Forms/`
- `Feature/` — `Http/ContentControllerTest.php`, `Integration/FormSubmissionFlowTest.php`,
  `Livewire/{NewsFull,ProjectsFull,PublicForm}Test.php`

## lang/

- `panel.php` в каждой локали — все админ-строки (`__('panel.*')`).
