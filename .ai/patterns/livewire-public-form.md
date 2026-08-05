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

## Маска телефона (тип поля `phone`)

Маска `+7 911 111 11 11` — клиентская, Alpine-компонент `phoneMask(model)` в
`resources/js/app.js`, навешивается в `components/form/fields/input.blade.php`
только при `type === 'phone'` (`x-on:input|focus|blur|keydown.backspace`).

Ключевое:

- **Синхронизация с Livewire — через `$wire.set(model, value, false)`**, а не
  через нативный `input`-эвент: `wire:model` слушает тот же эвент на том же
  элементе, порядок слушателей Alpine и Livewire не гарантирован. `false` —
  локальная запись без запроса на сервер, значение уедет при submit.
- `focus` на пустом поле подставляет `+7 `, `blur` очищает поле, если цифр
  не больше одной — иначе необязательное поле уедет в валидацию с `+7 `.
- `keydown.backspace` на разделителе сдвигает каретку влево, иначе Backspace
  «съедает» пробел, который маска тут же возвращает.
- `mapInputType()` в презентере отдаёт для `phone` тип `tel`.

Серверная проверка — ветка `phone` в `FormRulesBuilder`:
`regex:/^\+7 \d{3} \d{3} \d{2} \d{2}$/`, сообщение `panel.invalid_phone`
(не перетирает кастомное сообщение из `FormField::rules`).

## Защита от спама

- **Honeypot** `website` — см. выше.
- **RateLimiter** в `SubmitFormAction::handle()`: `forms:{form_id}:{ip}` — 5 попыток
  за 300 секунд, дальше `ValidationException` с ключом `form`
  и сообщением `panel.too_many_attempts`.
