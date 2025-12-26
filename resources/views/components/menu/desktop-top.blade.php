@props([
    'menuItems' => [],
])

<div class="hidden md:flex items-center space-x-8">
    <ul class="flex space-x-6">
        
        @foreach($menuItems as $item)
            
            @php($hasChildren = !empty($item['children']))
            
            <li class="relative group flex flex-row mr-4 items-center
                   before:content-['//'] first:before:hidden before:text-accent
                   before:mr-4"
            >
                <x-menu.desktop-link
                        href="{{$hasChildren ? '' : $item['href']}}"
                        blank="{{$item['blank']}}"
                        :has-children="$hasChildren"
                >
                    {{$item['label']}}
                    
                    @if ($hasChildren)
                        <p
                                class="font-carlito text-text transition-all duration-150
                                     group-hover:text-accent-add absolute -right-2.5 -top-2"
                        >
                            +
                        </p>
                    @endif
                    
                </x-menu.desktop-link>
                
                @if ($hasChildren)
                    <ul class="absolute -left-4 -top-4 p-8 pt-14 pb-4 z-10 bg-white shadow-lg
                               border border-accent-add  pointer-events-none
                               transition-all duration-200 opacity-0 invisible
                               group-hover:opacity-100 group-hover:visible group-hover:pointer-events-auto"
                    >
                        @foreach ($item['children'] as $child)
                            <li class="inline-block pb-3">
                                <x-menu.desktop-link
                                        href="{{ $child['href'] }}"
                                        blank="{{ $child['blank'] }}"
                                >
                                    {{ $child['label'] }}
                                </x-menu.desktop-link>
                            </li>
                        @endforeach
                    </ul>
                @endif
            
            </li>
        @endforeach
    
    </ul>
</div>