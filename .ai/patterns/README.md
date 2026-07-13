# patterns/

Переиспользуемые паттерны кода — «как мы это делаем здесь».

- [blocks-renderer.md](blocks-renderer.md) — интерфейс `BlockRenderer`, `key()`, `version()`, диспетчеризация
- [has-block-sections.md](has-block-sections.md) — three-section модель (`top/main/bottom_section` + cache id)
- [filament-block.md](filament-block.md) — статический Filament Block-компонент с локализацией и 12-колоночной сеткой
- [enum-with-color-label.md](enum-with-color-label.md) — enum-статус, реализующий `HasColor`/`HasLabel` из Filament
- [cache-flush-on-save.md](cache-flush-on-save.md) — теговый флаш кэша в `booted()` при saved/deleted
- [role-access-resource.md](role-access-resource.md) — trait `RoleAccessResource` на Filament ресурсах
- [livewire-public-form.md](livewire-public-form.md) — публичная форма на Livewire 3 (WithFileUploads + honeypot + Presenter + Action)