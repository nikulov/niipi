<div class="absolute z-10 -left-8.5 top-full w-max pointer-events-none opacity-0 -translate-y-2 group-hover:translate-y-0 transition duration-300 ease-out group-hover:pointer-events-auto group-hover:opacity-100">
    <div class="h-6"></div>
    <ul class="w-full flex flex-col p-4 bg-[#f1f1f1] shadow-lg"
    >
        @foreach ($items as $child)
            
            <li class="w-full inline-block py-2.5 first:pt-0 last:pb-0 border-b last:border-b-0 border-accent">
                <x-menu.desktop-link
                        href="{{ $child['href'] }}"
                        blank="{{ $child['blank'] }}"
                        class="text-small"
                >
                    <span class="px-5">{{ $child['label'] }}</span>
                </x-menu.desktop-link>
            </li>
        
        @endforeach
        
    </ul>
    <div class="shadow-lg bg-accent w-full h-1.5 -scale-y-100"
         style="
                    clip-path: polygon(
                            6px 0,
                            100% 0,
                            100% 100%,
                            100% 100%,
                            0 100%,
                            0 6px);">
    </div>
</div>