# Паттерн: Filament Block-компонент

Классы под `app/Filament/Components/*.php` — статические, оборачивают
`Filament\Forms\Components\Builder\Block`.

## Форма класса

Опираться на `app/Filament/Components/Title.php`:

```php
final class Title
{
    public static function key(): string
    {
        return 'title';
    }

    public static function block(): Block
    {
        return Block::make(self::key())
            ->label(__('panel.title_label'))
            ->columnSpanFull()
            ->schema([
                Textarea::make('title')->label(__('panel.heading'))
                    ->autosize()->columnSpanFull()->trim()->required(),

                Select::make('type')->label(__('panel.heading_size'))
                    ->options(['h2' => __('panel.heading').' 2', 'h3' => __('panel.heading').' 3'])
                    ->required()->columnSpan(6),

                Select::make('position')->label(__('panel.position_title'))
                    ->default('left')
                    ->options([
                        'left'   => __('panel.left'),
                        'center' => __('panel.center'),
                        'right'  => __('panel.right'),
                    ])
                    ->required()->columnSpan(6),
            ])
            ->columns(12);
    }
}
```

## Правила

- **`key()` совпадает с ключом рендерера** в `app/Blocks/Renderers/{Name}Renderer.php`.
- **12-колоночная сетка** — крупным полям `->columnSpanFull()` или
  `->columnSpan(6)`.
- **Всё локализуется** через `__('panel.*')`.
- **Класс `final`** — от него не наследуются.
- **Регистрация** — в `app/Filament/Components/BlockRegistry/BlockRegistry.php`.
  Есть следующие статические методы:
    - `all()` — полный список для общих мест.
    - `topSection()` — блоки, допустимые в верхней секции
      (сейчас: `ImageTittleFullWidth`, `SliderFullWidth`).
    - `mainSection()` — основная секция (большинство блоков).
    - `bottomSection()` — низ (`NewsBlock`, `ProjectsBlock`).
    - `tabs()` — блоки, доступные **внутри** `TabsBlock` как вложенные.
    - `modal()` — блоки, доступные **внутри** `ModalBlock` как вложенные.

    Новый блок обычно добавлять в `all()` **и** в `mainSection()`. В `tabs()` /
    `modal()` — если предполагается вкладывать в Tabs/Modal.

- **BgForMainSection** — особый блок-настройка (`isSectionOptionBlock`), не
  рендерится через `BlockRenderRegistry`; данные достаются через
  `HasSectionOptions::getSectionOption()`. Пример — [../patterns/has-block-sections.md](has-block-sections.md).
