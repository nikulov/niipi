<nav class="bg-[url('/resources/images/layout/menu_bg.png')] px-inner-section-x flex bg-center bg-no-repeat min-h-30 justify-center items-end max-w-1600 w-full m-auto">
    <div class="container mx-auto max-w-1290 w-full pb-6">
        <div class="relative flex justify-end md:justify-center items-end">
            
            <div class="flex items-center absolute left-0">
                <a href="{{ route('home') }}"
                   class="">
                    <x-logo class="w-40 h-12 fill-[#5B8EAE]" />
                </a>
            </div>
            
            <x-menu.top/>
            
            <div class="flex items-center space-x-4 absolute right-6">
{{--                <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors duration-200">--}}
{{--                    <x-icon.icon-switcher-color />--}}
{{--                </a>--}}
{{--                <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors duration-200">--}}
{{--                   <x-icon.icon-search />--}}
{{--                </a>--}}
            </div>
            
        </div>
    </div>
</nav>