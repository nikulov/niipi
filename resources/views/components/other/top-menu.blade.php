<div class="hidden md:flex items-center space-x-8">
    <ul class="flex space-x-6">
        
        @foreach($menuItems as $item)
        <li class="">
            <x-other.menu-link
                    href="{{$item['href']}}"
                    blank="{{$item['blank']}}"
            >
                {{$item['label']}}
            </x-other.menu-link>
        </li>
        @endforeach
        
    </ul>
</div>