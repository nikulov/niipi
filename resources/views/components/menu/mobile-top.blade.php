@props([
    'menuItems' => [],
])

<div class="md:hidden relative"
     x-data="{ menuOpen: false }"
     @keydown.escape.window="menuOpen = false"
>
    <button type="button"
            class="text-accent hover:text-accent-add focus:outline-none transition-colors duration-200 cursor-pointer"
            @click="menuOpen = true"
    >
        <x-icon.icon-arrow-down class="w-6 h-6 fill-accent"/>
    </button>

    <div
            x-cloak
            x-show="menuOpen"
            
            x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="-translate-y-full"
            x-transition:enter-end="translate-y-0 "
            
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="-translate-y-full"
            
            class="transform fixed inset-0 z-50
            bg-background-dark bg-[url('/resources/images/layout/waves-mobile.png')] bg-cover bg-center"
    >
        <div class="flex flex-row justify-between items-end px-inner-section-x py-12">
            
            <div class="flex items-center">
                <a href="{{ route('home') }}"
                   class="">
                    <x-logo class="w-40 h-12 fill-white"/>
                </a>
            </div>
            
            <button type="button"
                    class="text-white hover:text-[#60C0C3] focus:outline-none transition-all duration-150 cursor-pointer delay-1000 opacity-0 translate-y-20"
                    @click="menuOpen = false"
                    x-transition
                    :class="menuOpen ? 'opacity-100 !translate-y-0 pointer-events-auto' : ''"
            >
                <x-icon.icon-arrow-down class="w-6 h-6 fill-white rotate-180"/>
            </button>
        
        </div>
        
        <div class="flex flex-col justify-center">
            
            <div class="flex items-center justify-center space-x-8 bg-white/30">
                <ul class="w-full flex flex-col justify-center items-center space-x-6 px-inner-section-x">
                    
                    @foreach($menuItems as $item)
                        <li class="flex justify-center w-full m-0 border-b border-white last:border-0">
                            <x-menu.mobile-link
                                    href="{{$item['href']}}"
                                    blank="{{$item['blank']}}"
                            >
                                {{$item['label']}}
                            </x-menu.mobile-link>
                        </li>
                    @endforeach
                
                </ul>
            </div>
            
            <div class="flex items-center justify-between my-inner-section-y px-inner-section-x">
                <x-menu.mobile-contact-block url="#">
                    <x-icon.icon-mobile class="w-9 h-9 fill-white"/>
                </x-menu.mobile-contact-block>
                
                <x-menu.mobile-contact-block url="#">
                    <x-icon.icon-at class="w-9 h-9 fill-white"/>
                </x-menu.mobile-contact-block>
                
                <x-menu.mobile-contact-block url="#">
                    <x-logo.logo-tg class="w-9 h-9 fill-white"/>
                </x-menu.mobile-contact-block>
            </div>
            
            <a href="#" class="flex gap-8 justify-center items-center mb-inner-section-y px-inner-section-x">
                <x-icon.icon-point class="w-6 h-9 fill-[#4ECECB]"/>
                <span class="max-w-[220px] text-white text-small font-century font-bold">
                        129110, г. Москва ул. Гиляровского, дом 47, строение 3.
                    </span>
            </a>
            
            <hr class="w-full border-white">
            
            <span class="mt-2 text-white text-center text-small font-century">
                © {{ config('app.name') }}  {{ $year }}
            </span>
            
        </div>
    
    </div>
</div>
