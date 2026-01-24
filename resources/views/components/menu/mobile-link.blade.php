@props([
    'href' => '#',
    'blank' => false,
])

@if ($href === '#' || $href === '' || $href === '/#')
    <button
        type="button"
        {{
            $attributes->class([
                'menu-link-mobile menu-link-btn z-20 flex cursor-default flex-row items-center justify-center',
            ])
        }}
    >
        {{ $slot }}
    </button>
@else
    <a
        href="{{ $href }}"
        @if($blank) target="_blank" rel="noopener noreferrer" @endif
        {{
            $attributes->class([
                'menu-link-mobile',
                'menu-link-mobile-active' => trim(url()->current(), '/') === trim(url($href), '/'),
            ])
        }}
    >
        {{ $slot }}
    </a>
@endif
