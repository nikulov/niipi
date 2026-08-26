@props([
    'href' => '#',
    'blank' => false,
])

<a
    href="{{ $href }}"
    @if($blank) target="_blank" rel="noopener noreferrer" @endif
    class="dark:text-white-dark text-white hover:underline"
>
    {{ $slot }}
</a>
