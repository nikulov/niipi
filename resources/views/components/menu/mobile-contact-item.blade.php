@props(['url' => '#'])

<a
    href="{{ $url }}"
    class="flex aspect-square w-22 flex-col items-center justify-center gap-2 bg-[url('/resources/images/icon/icon-plan.svg')]"
>
    {{ $slot }}
</a>
