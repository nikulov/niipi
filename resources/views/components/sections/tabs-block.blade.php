@php
    $tabs = $tabs ?? [];
    $tabsHtml = $tabsHtml ?? [];
@endphp

<section class="px-inner-section-x my-inner-section-y mx-auto w-full max-w-1242" x-data="{ active: {{ $defaultIndex ?? 0 }} }">
    <div class="md:px-inner-section-x translate-y-px overflow-hidden">
        @foreach ($tabs as $index => $tab)
            <button
                type="button"
                class="text-big text-text dark:text-white-dark overflow-hidden border border-b-0 px-6 py-1.5 transition-colors focus:outline-none md:px-10"
                :class="active === {{ $index }} ? 'border-accent dark:border-accent-add-dark bg-background-light dark:bg-background-dark' : 'border-transparent'"
                @click="active = {{ $index }}"
            >
                {{ $tab['title'] }}
            </button>
        @endforeach
    </div>

    <div class="border-border dark:border-accent-add-dark bg-background-light dark:bg-background-dark border">
        @foreach ($tabs as $index => $tab)
            <div x-show="active === {{ $index }}" x-cloak>
                {!! $tabsHtml[$index] ?? '' !!}
            </div>
        @endforeach
    </div>
</section>
