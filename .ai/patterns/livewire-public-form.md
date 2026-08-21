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

## Загруженные файлы: путь приходит от клиента

Livewire принимает путь временного файла из снапшота на веру, поэтому перед
валидацией стоят два слоя (баг #23, разбор —
[plans/public-form-crashes](../plans/public-form-crashes/README.md)):

1. **`PublicForm::rejectMissingFiles()`** — идёт по самому `$uploads` (не по
   `viewData['fields']`: при пустом `viewData` обход полей был бы no-op),
   выбрасывает `TemporaryUploadedFile`, у которых `exists()` — ложь, кладёт
   очищенное обратно в `$this->uploads`, пишет `Log::warning` и кидает
   `ValidationException` на затронутые поля. Молчать нельзя: у необязательного
   поля заявка ушла бы без вложения.
2. **`SubmitFormAction`** — `try/catch (FilesystemException)` вокруг
   `validate()` с `report($e)`. Сетка: `FilesystemAdapter::size()` кидает даже
   при `'throw' => false` у диска, а правило `max:` его зовёт.

Третье место — `SubmissionFilesStorer` после `store()` проверяет
`exists()`: `TemporaryUploadedFile::storeAs()` выбрасывает результат `put()`,
поэтому неудачная запись иначе становится строкой в БД с путём в никуда
(баг #42). Подробности про флаги дисков —
[manual/filesystem-disks.md](../manual/filesystem-disks.md).

## Валидация

Правила формируются в `App\Services\Forms\FormRulesBuilder` на основе:

- `FormField::rules` (assoc-массив `правило => сообщение` или список правил)
- `FormField::extra` (для `type=file` — `multiple`, `max_files`, `max_size_kb`, `accept_mimes`)
- `FormField::required`, `FormField::type` (`text`/`email`/`select`/`radio`/`checkbox`/`file`)

Ошибки — через `ValidationException` (import из
`Illuminate\Validation\ValidationException`).

## Маска телефона (тип поля `phone`)

Маска `+7 (452) 354-32-53` — клиентская, Alpine-компонент `phoneMask(model)` в
`resources/js/app.js`, навешивается в `components/form/fields/input.blade.php`
только при `type === 'phone'` (`x-on:input|focus|blur|keydown.backspace`).

Ключевое:

- **Синхронизация с Livewire — через `$wire.set(model, value, false)`**, а не
  через нативный `input`-эвент: `wire:model` слушает тот же эвент на том же
  элементе, порядок слушателей Alpine и Livewire не гарантирован. `false` —
  локальная запись без запроса на сервер, значение уедет при submit.
- `focus` на пустом поле подставляет `+7 `, `blur` очищает поле, если цифр
  не больше одной — иначе необязательное поле уедет в валидацию с `+7 `.
- `keydown.backspace` сдвигает каретку влево через **все** подряд идущие
  разделители (`) ` — это два символа), иначе Backspace «съедает» разделитель,
  который маска тут же возвращает. Внутрь `+7` удаление не пускаем.
- `mapInputType()` в презентере отдаёт для `phone` тип `tel`.

Серверная проверка — ветка `phone` в `FormRulesBuilder`:
`regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/`, сообщение `panel.invalid_phone`
(не перетирает кастомное сообщение из `FormField::rules`).

**Хранение — `+74523543253`, без маски.** Приведение — ветка `phone` в
`SubmissionDataNormalizer` (вызывается в `SubmitFormAction` после валидации,
до `SubmissionCreator`), поэтому в БД, письмах и Filament номер компактный,
а в state Livewire остаётся форматированный.

Нормализуем **на сервере, а не в `$wire.set`**: если держать в state
`+74523543253`, а в инпуте показывать маску, то morph Livewire на любом ответе
сервера перезапишет `value` инпута состоянием и сотрёт маску.

## Защита от спама

- **Honeypot** `website` — см. выше.
- **RateLimiter** в `SubmitFormAction::handle()`: `forms:{form_id}:{ip}` — 5 попыток
  за 300 секунд, дальше `ValidationException` с ключом `form`
  и сообщением `panel.too_many_attempts`.
