# Добавить новый тип поля в form builder

Кастомный form builder этого проекта хранит поля в `FormField` и рендерит через
`App\Livewire\Forms\PublicForm` (не путать с Filament Forms).

## Модели
- `App\Models\Form` — конфиг формы (title, applicant_type, success_message,
  user_mail_attachments, is_active).
- `App\Models\FormField` — поле (type, name, label longtext, default, sort,
  is_enabled, options, validation).

## Где живёт логика
- Публичный компонент — `app/Livewire/Forms/PublicForm.php`.
- Blade-шаблон — `resources/views/livewire/forms/`.
- Presenter — `App\Presenters\Forms\PublicFormPresenter` (готовит `viewData`).
- Отправка — `App\Actions\Forms\SubmitFormAction`.

## Шаги для нового типа
1. **Blade**: добавить partial под новый `type` в `resources/views/livewire/forms/`
   (или в существующий switch по типу). Смотреть, как отрисованы уже имеющиеся
   типы — `select`, `radio` (`PublicForm::applySelectAndRadioDefaults()` учитывает
   их дефолты).
2. **Presenter**: если типу нужны дополнительные данные для рендера — расширить
   `PublicFormPresenter::present()`.
3. **Валидация**: правила должны формироваться внутри `SubmitFormAction` /
   `PublicForm::submit()` на основе `FormField::validation`.
4. **Filament-конфиг поля**: тип поля выбирается в админке. Обновить схему
   ресурса `app/Filament/Resources/Forms/Schemas/` + RelationManager полей.
5. **Дефолты**: если тип — «пред-выбираемый» (select/radio/checkbox), убедись,
   что `applySelectAndRadioDefaults()` покрывает его или расширь метод.

## Файлы
- Если поле принимает вложения — модель `FormSubmissionFile` и трейт
  `Livewire\WithFileUploads` уже подключены в `PublicForm`.

## Тесты
- `tests/Feature/` — путь: рендер компонента → submit → корректная запись в
  `FormSubmission` + вложения.