@props(['type' => 'button', 'label' => ''])

<button
    type="{{ $type }}"
    {{
        $attributes->merge([
            'class' => 'btn-primary group btn btn-primary-bg cursor-pointer no-underline focus:outline-none',
        ])
    }}
>
    <div
        class="btn-primary-bg absolute top-0.75 -left-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
    ></div>

    <span class="btn-primary-text btn-text">{{ $label }}</span>

    <div
        class="btn-primary-bg absolute -right-0.75 bottom-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
    ></div>
</button>
