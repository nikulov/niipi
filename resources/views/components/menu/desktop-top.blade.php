@props([
    'menuItems' => [],
])

<div class="hidden md:flex items-center space-x-8">
    <ul class="flex space-x-6">
        
        @foreach($menuItems as $item)
            
            @php($hasChildren = !empty($item['children']))
            
            <li class="relative group flex flex-row mr-4 items-center
                   after:content-['//'] last:after:hidden after:text-accent dark:after:text-accent-add-dark
                   after:ml-4"
            >
                <x-menu.desktop-link
                        href="{{$hasChildren ? '' : $item['href']}}"
                        blank="{{$item['blank']}}"
                        :has-children="$hasChildren"
                >
                    {{$item['label']}}
                </x-menu.desktop-link>
                
                @if ($hasChildren)
                    <x-menu.desktop-childe-block :items="$item['children']"/>
                @endif
            
            </li>
            
        @endforeach
    
    </ul>
</div>