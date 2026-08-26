@php
    $paginator = $getMediaFiles($makeGetUtility());
    $multiple = $getMultiple();
    $statePath = $getStatePath();

    $files = $paginator
        ->getCollection()
        ->map(
            fn ($f) => [
                'id' => $f->id,
                'path' => $f->path,
                'filename' => $f->filename,
                'title' => $f->title,
                'url' => $f->url,
                'type' => $f->type->value,
                'human_size' => $f->human_size,
            ],
        )
        ->values()
        ->all();

    $lastPage = $paginator->lastPage();
    $total = $paginator->total();
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();

    $pageStatePath = preg_replace('/[^.]+$/', 'media_page', $statePath);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            selected: @entangle($statePath),
            page: @entangle($pageStatePath).live,
            multiple: @js($multiple),

            isSelected(id) {
                if (this.multiple) {
                    return Array.isArray(this.selected) && this.selected.includes(id)
                }
                return this.selected === id
            },

            toggle(id) {
                if (this.multiple) {
                    if (! Array.isArray(this.selected)) this.selected = []
                    const idx = this.selected.indexOf(id)
                    if (idx === -1) {
                        this.selected = [...this.selected, id]
                    } else {
                        this.selected = this.selected.filter((i) => i !== id)
                    }
                } else {
                    this.selected = this.selected === id ? null : id
                }
            },
        }"
        class="space-y-3"
    >
        <div class="grid max-h-[60vh] grid-cols-4 gap-2 overflow-y-auto p-1 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10">
            @forelse ($files as $file)
                <div
                    @click="toggle({{ $file['id'] }})"
                    class="relative cursor-pointer rounded-lg border-2 p-1 transition-all duration-150 hover:shadow-md"
                    :class="isSelected({{ $file['id'] }})
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10 ring-2 ring-primary-500/50'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
                >
                    <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded bg-gray-100 dark:bg-gray-800">
                        @if ($file['type'] === 'image')
                            <img
                                src="{{ $file['url'] }}"
                                alt="{{ $file['filename'] }}"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />
                        @else
                            <span class="text-3xl">{{ $file['type'] === 'document' ? '📄' : '📎' }}</span>
                        @endif
                    </div>

                    <p
                        class="mt-1 truncate text-center text-xs text-gray-600 dark:text-gray-400"
                        title="{{ $file['filename'] }} ({{ $file['human_size'] }})"
                    >
                        {{ $file['title'] ?: $file['filename'] }}
                    </p>

                    <div
                        x-show="isSelected({{ $file['id'] }})"
                        x-transition
                        class="bg-primary-500 absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full text-white shadow"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('panel.media_no_files_found') }}
                </div>
            @endforelse
        </div>

        @if ($total > 0)
            <div class="flex flex-col items-center justify-between gap-2 sm:flex-row">
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $from }}–{{ $to }} / {{ $total }}</div>

                @if ($lastPage > 1)
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="if (page > 1) page = page - 1"
                            :disabled="page <= 1"
                            class="rounded-md bg-white px-3 py-1.5 text-sm text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/20 dark:hover:bg-white/10"
                        >
                            ←
                        </button>
                        <span class="text-sm text-gray-700 dark:text-gray-200">
                            <span x-text="page"></span>
                            / {{ $lastPage }}
                        </span>
                        <button
                            type="button"
                            @click="if (page < {{ $lastPage }}) page = page + 1"
                            :disabled="page >= {{ $lastPage }}"
                            class="rounded-md bg-white px-3 py-1.5 text-sm text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white/5 dark:text-gray-200 dark:ring-white/20 dark:hover:bg-white/10"
                        >
                            →
                        </button>
                    </div>
                @endif

                @if ($multiple)
                    <div x-show="Array.isArray(selected) && selected.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('panel.media_selected') }}:
                        <span x-text="selected.length"></span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-dynamic-component>
