# Livewire 3 — заметки

Версия: `livewire/livewire: ^3.7`.

## Где живут компоненты

- `app/Livewire/Components/` — UI-фрагменты (`ProjectsFull`, `NewsFull`,
  `AbstractContentFull`).
- `app/Livewire/Forms/` — формы (`PublicForm`).
- Шаблоны — `resources/views/livewire/components/`, `resources/views/livewire/forms/`.

## Публичные формы

См. [../patterns/livewire-public-form.md](../patterns/livewire-public-form.md).
Ключевые приёмы:

- `use WithFileUploads;` для загрузок файлов.
- Honeypot-поле `$website` — первая проверка в `submit()`.
- Компонент — тонкий; тяжёлое — в Presenter (`App\Presenters\Forms\*`) и Action
  (`App\Actions\Forms\*`), приходят через DI-параметры метода.

## componentKey

Если один тип компонента может рендериться несколько раз на странице (например,
две формы на одной странице), передавать уникальный `componentKey` в `mount()`.
`PublicForm` дефолтит его в `form:{id}`.

## Мультипотоковая перезагрузка блоков

Livewire-блоки используются как рендереры некоторых типов
(`ProjectsFull`, `NewsFull`) — соответствующие Renderer-классы рендерят
компонент через Blade `@livewire(...)` или его аналог.

## Валидация

Через `ValidationException` из `Illuminate\Validation\ValidationException` —
компонент прокидывает исключение, Livewire отрисовывает ошибки.

## Публичные свойства пишутся клиентом

Чексумма Livewire покрывает **снапшот, но не карту `updates`** — она
накладывается уже после гидрации. `HandleComponents::updateProperties()`
проходит по всем присланным путям и зовёт `updateProperty()`; единственный
барьер — атрибут `#[Locked]`, чей `BaseLocked::update()` кидает
`CannotUpdateLockedPropertyException`.

Практический вывод: любое публичное свойство без `#[Locked]` (включая
вложенные пути вида `viewData.fields.0.file.multiple`) клиент может
переписать на что угодно. Всё, что компонент считает доверенным состоянием,
должно быть либо `#[Locked]`, либо перечитываться из БД, либо читаться в
Blade через `?? default`.

Разбор реального случая — [../plans/bug-report-2026-08-04.md](../plans/bug-report-2026-08-04.md), пункт #24.

## Загруженные файлы переживают запрос строкой

`TemporaryUploadedFile` не хранится между запросами как объект: при дегидрации
он сериализуется в `livewire-file:<filename>` (для `multiple` —
`livewire-files:[…]`), при гидрации восстанавливается через
`createFromLivewire()`. Путь считает
`FileUploadConfiguration::path($path, false)` =
`'livewire-tmp' . ($path ? '/' : '') . $path`.

Отсюда ловушка: файл, созданный с **пустым** путём, получает путь
`livewire-tmp`, сериализуется как `livewire-file:livewire-tmp`, а на следующем
запросе восстанавливается уже как `livewire-tmp/livewire-tmp`. Битое значение
живёт в снапшоте и воспроизводится при каждом ретрае, пока страницу не
перезагрузят.

Проверять размер такого файла опасно: `Illuminate\Filesystem\FilesystemAdapter::size()`
— единственный метод адаптера **без `try/catch`**, он игнорирует `'throw' => false`
из конфига диска и пробрасывает `League\Flysystem\UnableToRetrieveMetadata`
наружу. Соседние `mimeType()`, `move()`, `checksum()` исключение ловят и
возвращают `false`. Значит правило `max:` на файле может дать 500 вместо
ошибки валидации.

Разбор реального случая — [../plans/bug-report-2026-08-04.md](../plans/bug-report-2026-08-04.md), пункт #23.

## Кэш вьюшек

Livewire-шаблоны компилятся в `storage/framework/views/*.php`. Директива
`@source '../../storage/framework/views/*.php'` в `resources/css/app.css`
даёт Tailwind шанс увидеть используемые классы после сборки.

## Тесты Livewire

Компонентные тесты живут в `tests/Feature/Livewire/`
(`NewsFullTest`, `ProjectsFullTest`, `PublicFormTest`). Использовать
`Livewire::test(Component::class, [...])->set(...)->call('submit')`.
