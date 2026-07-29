# Паттерн: PublicForm (Livewire 3 + WithFileUploads)

Публичный Livewire-компонент, рендерящий кастомную форму.

## Скелет (см. `app/Livewire/Forms/PublicForm.php`)

```php
final class PublicForm extends Component
{
    use WithFileUploads;

    public Form $form;
    public array $viewData = [];
    public array $data = [];
    public array $uploads = [];
    public bool $submitted = false;
    public ?string $componentKey = null;
    public string $website = '';   // honeypot

    public function mount(int $formId, ?string $componentKey = null): void { ... }
    public function submit(SubmitFormAction $action): void { ... }
}
```

## Ключевые приёмы

- **`WithFileUploads`** — для файлов; временное хранилище Livewire.
- **`componentKey`** — уникальный идентификатор экземпляра компонента для DOM
  (нужен, если на странице может быть несколько форм). Дефолт — `form:{id}`.
- **Honeypot `website`** — если заполнен, помечаем как отправленное и молча
  ресетим (антибот). Первая проверка в `submit()`.
- **Presenter**: подготовка данных для view вынесена в
  `App\Presenters\Forms\PublicFormPresenter::present()`. Компонент не строит
  структуру рендера сам.
- **Action**: сохранение и все побочные эффекты — `SubmitFormAction` через
  DI-параметр метода `submit()`.
- **Дефолты select/radio**: `applySelectAndRadioDefaults()` вызывается в
  `mount()`. Если добавляешь новый «пред-выбираемый» тип — расширь метод.

## Загрузка формы

Только активные формы с активными полями:

```php
Form::query()
    ->whereKey($formId)
    ->where('is_active', true)
    ->with(['fields' => fn ($q) => $q->where('is_enabled', true)->orderBy('sort')])
    ->firstOrFail();
```

## Валидация

Правила формируются в `App\Services\Forms\FormRulesBuilder` на основе:

- `FormField::rules` (assoc-массив `правило => сообщение` или список правил)
- `FormField::extra` (для `type=file` — `multiple`, `max_files`, `max_size_kb`, `accept_mimes`)
- `FormField::required`, `FormField::type` (`text`/`email`/`select`/`radio`/`checkbox`/`file`)

Ошибки — через `ValidationException` (import из
`Illuminate\Validation\ValidationException`).

## Защита от спама

- **Honeypot** `website` — см. выше.
- **RateLimiter** в `SubmitFormAction::handle()`: `forms:{form_id}:{ip}` — 5 попыток
  за 300 секунд, дальше `ValidationException` с ключом `form`
  и сообщением `panel.too_many_attempts`.
