@props([
    'menuItems' => [],
])

<div class="md:hidden relative w-full h-auto"
     x-data="{
        menuOpen: false,
        scrollY: 0,
        init() {
            this.$watch('menuOpen', (v) => {
                if (v) {
                    this.scrollY = window.scrollY;

                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${this.scrollY}px`;
                    document.body.style.left = '0';
                    document.body.style.right = '0';
                    document.body.style.width = '100%';
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.left = '';
                    document.body.style.right = '';
                    document.body.style.width = '';
                    document.body.style.overflow = '';

                    window.scrollTo(0, this.scrollY);
                }
            });
        }
    }"
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
            bg-background-dark bg-[url('/resources/images/layout/waves-mobile.png')] bg-cover bg-center
            overflow-y-auto overscroll-contain touch-pan-y"
    >
        <div class="flex flex-row justify-between items-end px-inner-section-x py-12">
            
            <div class="flex items-center">
                <a href="{{ route('home') }}"
                   class="">
                    <x-logo class="w-40 h-12 fill-white"/>
                </a>
            </div>
            
            <button type="button"
                    class="text-white hover:text-accent-add focus:outline-none transition-all duration-150 cursor-pointer delay-1000 opacity-0 translate-y-20"
                    @click="menuOpen = false"
                    x-transition
                    :class="menuOpen ? 'opacity-100 translate-y-0! pointer-events-auto' : ''"
            >
                <x-icon.icon-arrow-down class="w-6 h-6 fill-white rotate-180"/>
            </button>
        
        </div>
        
        <div class="flex flex-col justify-center">
            
            <div class="flex items-center justify-center space-x-8 bg-white/30">
                <ul class="w-full flex flex-col justify-center items-center space-x-6 px-inner-section-x"
                    x-data="{ openIndex: null }"
                >
                    
                    @foreach($menuItems as $i => $item)
                        
                        @php($hasChildren = !empty($item['children']))
                        
                        
                        
                        <li class="relative group flex flex-row justify-center items-center w-full m-0 border-b border-white last:border-0"
                            @if($hasChildren)
                                @click="openIndex = openIndex === {{ $i }} ? null : {{ $i }}"
                            @endif
                            :class="openIndex === {{ $i }} ? 'border-b-0' : ''"
                        >
                            <x-menu.mobile-link
                                    href="{{$hasChildren ? '' : $item['href']}}"
                                    blank="{{$item['blank']}}"
                            >
                                {{$item['label']}}
                                
                                @if($hasChildren)
                                    <p class="font-carlito text-text ml-1.5 -translate-y-2"
                                       :class="openIndex === {{$i}} ? 'text-accent-add' : '' "
                                    >+</p>
                                @endif
                            
                            </x-menu.mobile-link>
                        </li>
                        
                        @if ($hasChildren)
                            <ul class="w-full flex flex-col justify-center items-center mr-0 px-inner-section-x border-white"
                                x-cloak
                                x-show="openIndex === {{$i}}"
                                
                                x-collapse.duration.100ms
                                
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 border-b-0"
                                x-transition:enter-end="opacity-100 border-b"
                                
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 border-b-0"
                                x-transition:leave-end="opacity-0 border-b-0"
                                
                                :class="openIndex === {{$i}}  ? 'border-b' : 'border-b-0'"
                            >
                                
                                @foreach ($item['children'] as $child)
                                    <li class="inline-block w-full last:pb-6">
                                        <x-menu.mobile-link
                                                href="{{ $child['href'] }}"
                                                blank="{{ $child['blank'] }}"
                                                class="capitalize py-4"
                                        >
                                            {{ $child['label'] }}
                                        </x-menu.mobile-link>
                                    </li>
                                @endforeach
                            
                            </ul>
                        @endif
                    
                    @endforeach
                
                </ul>
            </div>
            
            <x-other.footer-mobile/>
        
        </div>
    
    </div>
</div>
