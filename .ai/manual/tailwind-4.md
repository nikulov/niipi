# Tailwind 4 — заметки

Стек: Tailwind 4.1 + `@tailwindcss/vite`, без `tailwind.config.js`.
Единственный источник правды — `resources/css/app.css`.

## Регистрация источников
В Tailwind 4 нет `content` в JS-конфиге — сканирование задаётся директивами
`@source` прямо в CSS. Из `resources/css/app.css`:

```css
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';
```

При добавлении новых директорий с классами — добавлять сюда `@source`.

## Тема
`@theme { ... }` определяет дизайн-токены как CSS-переменные,
превращаемые в утилиты. В проекте:

- Цвета: `--color-primary`, `--color-accent`, `--color-accent-add`,
  `--color-background-dark`, `--color-background-light` и их `*-dark` варианты.
- Шрифты: `--font-carlito`, `--font-century`, `--font-kosugi`.
- Контейнеры: `--container-1600`, `--container-1290`, `--container-1242`.
- Отступы секций: `--spacing-inner-section-y/x`, `--spacing-after-title`.

Утилита `bg-primary` появляется автоматически из `--color-primary` и т.п.

## Тёмная тема
Кастомный вариант вместо стандартного `dark:`:

```css
@custom-variant dark (&:where(.dark, .dark *));
```

Это класс-стратегия: `dark` включается наличием класса `dark` на предке.

## Кастомные утилиты
Создаются директивой `@utility`, а не `@layer utilities`:

```css
@utility text-normal {
    @apply font-carlito text-[16px] leading-[22.4px] tracking-[0.2px];
}

@utility px-inner-section-x {
    padding-left: var(--spacing-inner-section-x);
    padding-right: var(--spacing-inner-section-x);
}
```

Правила:
- `@utility` даёт **полноценную утилиту** — работает с variants (`hover:`,
  `md:`, `dark:`).
- Именование — существующие: `text-{size}`, `text-{scope}` (`text-btn-corner`,
  `text-footer-title`), `acc-arrow-*`, `grid-layout*`.

## Слои
`@layer base { ... }` — сброс/типографика (`h1..h3`, `body`, `blockquote`).
`@layer components { ... }` — компонентные классы (`btn`, `card-wrapper`,
`menu-link*`, `swiper-*`, `rich-editor *`).

## Мобильные брейкпоинты
Стандартные Tailwind (`sm md lg xl 2xl`). Меди-переопределения CSS-переменных
допустимы:

```css
@media (max-width: 768px) {
    :root {
        --spacing-inner-section-x: 24px;
    }
}
```

## Форматирование
Prettier с `prettier-plugin-tailwindcss` сортирует классы. Запуск:
`vendor/bin/sail npm run format`.

## Что не делать
- Не создавать `tailwind.config.js` — конфига в JS в этом проекте нет и не должно быть.
- Не использовать `@layer utilities { ... }` для новых утилит — только `@utility`.
- Не хардкодить цвета из палитры — использовать токены `bg-primary`, `text-accent-add` и т.п.