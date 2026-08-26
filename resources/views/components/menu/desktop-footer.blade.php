<div class="hidden grid-cols-1 gap-x-10 gap-y-1 text-sm leading-relaxed text-white md:grid">
    <ul class="grid list-disc grid-cols-2 gap-x-6 gap-y-0 pl-5">
        @foreach ($menuItems as $item)
            <li class="">
                <x-menu.desktop-footer-link href="{{$item['href']}}" blank="{{$item['blank']}}">
                    {{ $item['label'] }}
                </x-menu.desktop-footer-link>
            </li>
        @endforeach
    </ul>
</div>
