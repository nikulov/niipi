@props([
    'url' => '#',
    'text' => 'кнопка',
    'type' => 'btn-primary',
    'blank' => false,
])

<a
    href="{{ $url }}"
    {{ $attributes->class("group btn no-underline {$type}-bg") }}
    @if($blank) target="_blank" rel="noopener noreferrer"@endif
>
    <div
        class="{{ $type }}-bg absolute top-0.75 -left-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
    ></div>

    <span class="{{ $type }}-text btn-text">{{ $text }}</span>

    <div
        class="{{ $type }}-bg absolute -right-0.75 bottom-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
    ></div>
</a>
