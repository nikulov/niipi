@props([
    'menuItems' => [],
])

<div class="md:hidden relative"
     x-data="{ menuOpen: true }"
>
    <button type="button"
            class="text-gray-600 hover:text-[#60C0C3] focus:outline-none transition-colors duration-200 cursor-pointer"
            @click="menuOpen = true"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    
    <div
            class="fixed inset-0 bg-background-dark z-50
            bg-[url('/resources/images/layout/waves-mobile.png')] bg-cover bg-center"
            x-show="menuOpen"
            x-transition.opacity
            x-cloak
    >
        <div class="flex flex-row justify-between px-inner-section-x py-inner-section-y">
            
            <div class="flex items-center">
                <a href="{{ route('home') }}"
                   class="">
                    <x-logo class="w-40 h-12 fill-white"/>
                </a>
            </div>
            
            <button type="button"
                    class="text-white hover:text-[#60C0C3] focus:outline-none transition-colors duration-200 cursor-pointer"
                    @click="menuOpen = true"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        
        </div>
        
        <div>
            
            <div class="flex items-center justify-center space-x-8 bg-[#A7C1D2]">
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
            
            <a href="#" class="flex gap-8 justify-center items-center my-inner-section-y px-inner-section-x">
                <x-icon.icon-point class="w-6 h-9 fill-[#4ECECB]"/>
                <span class="max-w-[220px] text-white text-small font-century font-bold">
                        129110, г. Москва ул. Гиляровского, дом 47, строение 3.
                    </span>
            </a>
            
            <div class="flex items-center justify-between px-inner-section-x">
                <x-menu.mobile-contact-block>
                    <x-icon.icon-mobile class="w-9 h-9 fill-white"/>
                    <a href="#" class="text-white text-small font-bold">+7 (495) 242-77-07</a>
                </x-menu.mobile-contact-block>
                
                <x-menu.mobile-contact-block>
                    <x-icon.icon-at class="w-9 h-9 fill-white"/>
                    <a href="#" class="text-white text-small font-bold">niipi@mosreg.ru</a>
                </x-menu.mobile-contact-block>
                
                <x-menu.mobile-contact-block>
                    <x-logo.logo-tg class="w-9 h-9 fill-white"/>
                    <a href="#" class="text-white text-small font-bold">t.me/niipigrad</a>
                </x-menu.mobile-contact-block>
            </div>
        
        </div>
    
    </div>
</div>
