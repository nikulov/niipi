# Шаг 07. Медиа-пикер (Action + Field + Blade)

## Концепт

Три компонента:
- **`MediaPickerAction`** — фабрика `Filament\Actions\Action`
  (возвращает Closure, чтобы каждый клон компонента внутри Repeater
  получал свой инстанс). Модалка с формой из TextInput (поиск),
  Hidden (страница) и кастомного поля `MediaGrid`. На confirm — читает
  выбранные id → достаёт `path` из `MediaFile` → пишет в целевое поле
  через `Set`.
- **`MediaGrid`** — кастомное поле-компонент (`Filament\Forms\Components\Field`),
  умеющее строить пагинированный запрос по `MediaFile` и рендерить свою
  Blade-вьюху.
- **Blade `forms.components.media-grid`** — грид на Alpine с выбором
  (single/multi), пагинацией через `@entangle('media_page').live`.

## Что делаем

1. **`MediaPickerAction`** — код из
   [_source-prompt.md](_source-prompt.md#шаг-9-медиа-пикер-modal--grid)
   дословно. Возвращает Closure — важно.

2. **`MediaGrid`** — код из промта дословно.

3. **Blade-шаблон** — код из промта дословно. Проверить, что
   `$makeGetUtility()` доступен в Filament 4 в контексте Field-вьюхи —
   в v4 это метод компонента, отдаёт `Get` utility, привязанный к
   локальному state-контейнеру.

## Файлы

- **NEW** `app/Filament/Forms/Components/MediaPickerAction.php`
- **NEW** `app/Filament/Forms/Components/MediaGrid.php`
- **NEW** `resources/views/forms/components/media-grid.blade.php`

## References

- Существующий кастомный форм-компонент — `app/Filament/Forms/Components/UrlInput.php`
  и `CustomRepeater.php`. Стилевой ориентир.
- Поле выбора медиа доставляет `path` в целевое поле `FileUpload`. Оно
  сохраняется в БД как строка — единая ниточка через всю систему
  (никаких id-в-контенте).

## Gotchas

- **Closure для `hintAction`.** `MediaPickerAction::make()` возвращает
  `Closure`, а не `Action`. В Filament 4 `hintAction()` принимает
  `Action | Closure(): Action` — так что подпись правильная.
- **`@entangle($statePath)`** внутри Alpine — работает через Livewire
  wire model. В v3 синтаксис `@entangle(...).live` без изменений.
- **`makeGetUtility()`** — метод Field-компонента; отдаёт замыкание с
  локальным `Get` (видит `media_search`, `media_page`). Для теста —
  открыть модалку, ввести в поиск → сетка должна обновиться после 500ms
  дебаунса.
- **Search escaping** — `str_replace(['%', '_'], ['\%', '\_'], $search)`
  экранирует MySQL LIKE-специальные символы. OK.
- **`Storage::disk('public')->url(...)`** — публичный URL. Проверь, что
  `config/filesystems.php:public.url` выставлен (в проекте по умолчанию
  так). Иначе картинки в гриде не подгрузятся.
- **`maxSize` в `MediaPickerAction::make(...)`** передаётся в килобайтах
  (совпадает с `FileUpload::maxSize()`).

## Checklist

- [ ] Все 3 файла созданы.
- [ ] На форме с `FileUpload::make('x')->hintAction(MediaPickerAction::make('x', imagesOnly: true))`
      кнопка «Выбрать из библиотеки» открывает модалку.
- [ ] В модалке отображается сетка с превью изображений.
- [ ] Поиск фильтрует список после 500ms.
- [ ] Пагинация переключает страницы (кнопки ←/→).
- [ ] Клик по карточке подсвечивает её (single mode).
- [ ] На confirm выбранный `path` попадает в целевое поле `FileUpload`.
- [ ] Multi-mode (`multiple: true`) позволяет выбрать несколько.
- [ ] `pint --dirty` без замечаний.
