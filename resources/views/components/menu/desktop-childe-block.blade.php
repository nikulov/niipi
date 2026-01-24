<div
    class="pointer-events-none absolute top-full -left-8.5 z-30 w-max -translate-y-2 opacity-0 transition duration-300 ease-out group-hover:pointer-events-auto group-hover:translate-y-0 group-hover:opacity-100"
>
    <div class="h-6"></div>
    <ul class="dark:bg-background-dark flex w-full flex-col bg-[#f1f1f1] p-4 shadow-lg">
        @foreach ($items as $child)
            <li class="border-accent dark:border-accent-add-dark inline-block w-full border-b py-2.5 first:pt-0 last:border-b-0 last:pb-0">
                <x-menu.desktop-link href="{{ $child['href'] }}" blank="{{ $child['blank'] }}" class="text-small">
                    <span class="px-5">{{ $child['label'] }}</span>
                </x-menu.desktop-link>
            </li>
        @endforeach
    </ul>
    <div
        class="bg-accent h-1.5 w-full -scale-y-100 shadow-lg"
        style="clip-path: polygon(6px 0, 100% 0, 100% 100%, 100% 100%, 0 100%, 0 6px)"
    ></div>
</div>
