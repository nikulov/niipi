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

## Кэш вьюшек
Livewire-шаблоны компилятся в `storage/framework/views/*.php`. Директива
`@source '../../storage/framework/views/*.php'` в `resources/css/app.css`
даёт Tailwind шанс увидеть используемые классы после сборки.

## Тесты Livewire
Компонентные тесты живут в `tests/Feature/Livewire/`
(`NewsFullTest`, `ProjectsFullTest`, `PublicFormTest`). Использовать
`Livewire::test(Component::class, [...])->set(...)->call('submit')`.