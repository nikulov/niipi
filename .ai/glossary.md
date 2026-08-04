# Глоссарий

- **NIiPI** — название сайта/проекта.
- **Блок** — единица блочного контента страницы. Пара «конфиг в админке» +
  «рендерер в `app/Blocks/Renderers/`». См. [domain.md](domain.md).
- **Block Registry** — два реестра:
    - `App\Blocks\BlockRenderRegistry` — сопоставление тип → рендерер (публичная часть).
    - `app/Filament/Components/BlockRegistry/BlockRegistry.php` — конфиг блока в Filament (админка).
- **Post** — новость. Публично: `/news/{slug}`.
- **Project** — проект. Публично: `/projects/{slug}`.
- **Page** — статическая или составная страница. Публично: `/{slug}` или `/`.
- **FormSubmission** — сохранённая заявка (отправка публичной формы).
- **Sail** — Laravel Sail (Docker-обёртка). Все команды идут через `vendor/bin/sail`.
- **Valkey** — Redis-совместимый сервис в `compose.yaml` (открытый форк Redis).
- **Mailpit** — dev-перехватчик исходящих писем.
- **Pint** — Laravel Pint, PHP-форматтер (запуск: `sail bin pint --dirty`).
- **PublicForm** — Livewire-компонент публичной формы (`app/Livewire/Forms/PublicForm.php`).
