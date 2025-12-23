<div class="hidden md:grid grid-cols-1 gap-y-1 gap-x-10 text-sm leading-relaxed text-white">
    <ul class="grid grid-cols-2 gap-x-6 gap-y-2 list-disc pl-5">
        
        @foreach($menuItems as $item)
            <li class="">
                <x-other.menu-footer-link
                        href="{{$item['href']}}"
                        blank="{{$item['blank']}}"
                >
                    {{$item['label']}}
                </x-other.menu-footer-link>
            </li>
        @endforeach
    
    </ul>
</div>