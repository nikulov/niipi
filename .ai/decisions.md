# Архитектурные решения

Хронологический журнал заметных решений. Каждая запись — краткая причина.

## Filament 4 как единственная админка
Все CRUD-операции — через Filament Resources под `app/Filament/Resources/`.
Кастомные потребности закрываются `Filament/Forms/Components/` и
`Filament/Components/`, не отдельными контроллерами.

## Блочный контент через реестр
Тела Page/Post/Project — массив блоков. Каждый блок:
- рендерится классом из `app/Blocks/Renderers/`
- регистрируется в `App\Blocks\BlockRenderRegistry`
- имеет админ-конфиг в `app/Filament/Components/BlockRegistry/BlockRegistry.php`

Причина: единое место расширения — добавление блока не трогает контроллеры/шаблоны страниц.

## Публичные формы на Livewire
`App\Livewire\Forms\PublicForm` рендерит форму по конфигу модели `Form` и
её `FormField`. Action-логика вынесена в `App\Actions\Forms\*`.

## Единый ContentController + catch-all роут
Все публичные страницы (кроме `/news/*`, `/projects/*`) идут через один
контроллер и slug-роут `/{slug}` с exclude-паттерном для `admin|api|login|register`.

## Cookie `cookie_consent` вне encryptCookies
См. `bootstrap/app.php`. Причина: доступ к состоянию согласия из frontend без расшифровки.

## Инфраструктура — Sail
Все команды выполнять через `vendor/bin/sail`. Смотри [workflow.md](workflow.md).