@props([
    'menuItems' => [],
])

<div class="hidden md:flex items-center space-x-8">
    <ul class="flex space-x-6">
        
        @foreach($menuItems as $item)
        <li class="">
            <x-menu.desktop-link
                    href="{{$item['href']}}"
                    blank="{{$item['blank']}}"
            >
                {{$item['label']}}
            </x-menu.desktop-link>
        </li>
        @endforeach
        
    </ul>
</div>