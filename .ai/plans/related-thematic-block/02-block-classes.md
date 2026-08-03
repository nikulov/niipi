# 02 — Filament-компонент + Renderer + Blade + локализация

## Concept

Создаём тройку файлов «нового блочного типа» по рецепту
`.ai/skills/add-block.md`. Renderer — полиморфный (Post → NewsQuery,
Project → ProjectsQuery), Filament-схема — с фильтром категорий по
типу модели, Blade — 5 квадратных карточек в ряд + кнопка.

## What we do

### `app/Filament/Components/RelatedThematic.php`

```php
final class RelatedThematic
{
    public static function key(): string { return 'related-thematic'; }

    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.related_thematic_label'))
            ->columnSpanFull()
            ->columns(12)
            ->schema([
                Textarea::make('title')->label(__('panel.title'))
                    ->autosize()->columnSpan(4)
                    ->default(__('panel.related_thematic'))->required(),

                TextInput::make('limit')->label(__('panel.limit'))
                    ->columnSpan(2)->numeric()->default(5)->required(),

                TextInput::make('btnLabel')->label(__('panel.btn_label'))
                    ->columnSpan(6)
                    ->default(__('panel.related_thematic_all_btn'))->required(),

                Select::make('categoryIds')->label(__('panel.category'))
                    ->multiple()->preload()
                    ->options(fn ($livewire) => self::categoryOptions($livewire))
                    ->columnSpan(12)
                    ->helperText(__('panel.related_thematic_categories_hint')),
            ]);
    }

    public static function getDefaultBlock(): array
    {
        return [[
            'type' => self::key(),
            'data' => [
                'title'     => __('panel.related_thematic'),
                'limit'     => 5,
                'btnLabel'  => __('panel.related_thematic_all_btn'),
                // categoryIds намеренно НЕ задаём — берётся из модели
            ],
        ]];
    }

    private static function categoryOptions($livewire): array
    {
        $model = self::resolveModel($livewire);
        $type = match ($model) {
            Post::class    => CategoryType::Posts,
            Project::class => CategoryType::Projects,
            default        => null,
        };
        if ($type === null) return [];
        return Category::query()->where('type', $type->value)
            ->orderBy('name')->pluck('name', 'id')->toArray();
    }

    private static function resolveModel($livewire): ?string
    {
        // Edit-страница: $livewire->getRecord()::class
        if (method_exists($livewire, 'getRecord') && $livewire->getRecord()) {
            return $livewire->getRecord()::class;
        }
        // Create-страница: определить по классу ресурса
        if (method_exists($livewire, 'getResource')) {
            $resource = $livewire::getResource();
            return $resource::getModel();
        }
        return null;
    }
}
```

### `app/Blocks/Renderers/RelatedThematicRenderer.php`

```php
final class RelatedThematicRenderer implements BlockRenderer
{
    public function __construct(
        private readonly NewsQuery $newsQuery,
        private readonly ProjectsQuery $projectsQuery,
    ) {}

    public static function key(): string { return 'related-thematic'; }
    public static function version(): string { return '1'; }

    public function render(array $data, HasBlockSections $model, int $index): string
    {
        if (! $model instanceof Post && ! $model instanceof Project) {
            return '';
        }

        $limit = max(1, min(20, (int) ($data['limit'] ?? 5)));

        $categoryIds = $data['categoryIds'] ?? null;
        $categoryIds = is_array($categoryIds) && $categoryIds !== []
            ? array_values($categoryIds)
            : $model->categories()->pluck('categories.id')->all();

        if ($categoryIds === []) {
            return ''; // у записи нет категорий и в блоке не указаны — рендерить нечего
        }

        $isPost = $model instanceof Post;
        $items = $isPost
            ? $this->newsQuery->list($limit, $categoryIds, false, 'page', $model->getKey())
            : $this->projectsQuery->list($limit, $categoryIds, false, 'page', $model->getKey());

        if ($items->isEmpty()) {
            return '';
        }

        $cards = $items->map(fn ($m) => $isPost
            ? NewsBlockPresenter::make($m)
            : ProjectsBlockPresenter::make($m)
        )->toArray();

        // Кнопка «Смотреть все» — с первой категорией текущей записи
        $firstCategorySlug = $model->categories->first()?->slug;
        $btnUrl = $isPost
            ? url('news' . ($firstCategorySlug ? '?newsCategory=' . urlencode($firstCategorySlug) : ''))
            : url('projects' . ($firstCategorySlug ? '?projectsCategory=' . urlencode($firstCategorySlug) : ''));

        return view('components.sections.related-thematic', [
            'title'    => $data['title'] ?? '',
            'btnLabel' => $data['btnLabel'] ?? __('panel.related_thematic_all_btn'),
            'btnUrl'   => $btnUrl,
            'cards'    => $cards,
        ])->render();
    }
}
```

### `resources/views/components/sections/related-thematic.blade.php`

Секция типа inner (по образцу `category-list.blade.php`), сетка
`grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4`, квадратные карточки
с `aspect-square`, оверлей с обрезанным заголовком, вся карточка —
кликабельный `<a>`. Alt = `title` (fallback `config('app.name')`).
Кнопка через `<x-buttons.btn>` — по образцу `news-block.blade.php`.

### `lang/ru/panel.php` и `lang/en/panel.php`

- `related_thematic_label` — «Тематическая подборка» / «Related thematic»
- `related_thematic` — «По этой теме» / «Related»
- `related_thematic_all_btn` — «Смотреть все» / «View all»
- `related_thematic_categories_hint` — «Если не выбрано — берутся категории текущей записи» / «If empty — the record's own categories are used»

## Files

- **NEW** `app/Filament/Components/RelatedThematic.php`
- **NEW** `app/Blocks/Renderers/RelatedThematicRenderer.php`
- **NEW** `resources/views/components/sections/related-thematic.blade.php`
- **EDIT** `lang/ru/panel.php`
- **EDIT** `lang/en/panel.php`

## References

- `app/Filament/Components/NewsFull.php` — паттерн Select с категориями
- `app/Filament/Components/NewsBlock.php` — паттерн Textarea/TextInput
- `app/Blocks/Renderers/CategoryListRenderer.php` — паттерн полиморфного `instanceof Post|Project`
- `app/Blocks/Renderers/NewsFullRenderer.php` — паттерн клампа `limit`
- `resources/views/components/sections/category-list.blade.php` — inner-секция
- `resources/views/components/sections/projects-block.blade.php` — карточки с картинкой
- `.ai/skills/add-block.md` — общий рецепт
- `.ai/patterns/blocks-renderer.md`, `.ai/patterns/filament-block.md`
- `.ai/conventions.md` (раздел «Alt у картинок») — fallback правила

## Gotchas

- `pluck('categories.id')` — квалифицированное имя колонки, чтобы
  не ловить конфликт с `posts.id`/`projects.id` в BelongsToMany.
- `model->categories` — использовать `$model->categories` (загружает
  relation), не `$model->categories()->get()` — потому что уже могла
  быть eager-загрузка.
- Если у поста/проекта **нет** категорий и в блоке ничего не указано —
  возвращаем пустую строку (нечего показывать). Не падаем.
- Filament v4: `$livewire` в замыкании `->options()` — экземпляр
  `CreateRecord` или `EditRecord`. Для Create `getRecord()` возвращает
  `null` — тогда идём через `getResource()::getModel()`.
- Alt картинок — по конвенции `.ai/conventions.md`: fallback
  `$card['title']` → `config('app.name')`.

## Checklist

- [ ] Filament-компонент создан, `getDefaultBlock()` возвращает конфиг без `categoryIds`
- [ ] Renderer возвращает `''` для не-Post/Project и для пустого набора категорий
- [ ] `limit` кламппится 1..20
- [ ] `categoryIds` из data имеет приоритет над категориями модели
- [ ] Текущий пост/проект исключён из выдачи (через `excludeId` из шага 01)
- [ ] Кнопка ведёт на `/news?newsCategory=...` или `/projects?projectsCategory=...` с первой категорией модели
- [ ] Blade использует quadratные карточки 2/3/5, alt по конвенции
- [ ] Локализация ru + en добавлена
- [ ] `sail bin pint --dirty --format agent`
- [ ] `sail npm run format` (blade)
