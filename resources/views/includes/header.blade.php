<nav class="bg-[url('/resources/images/layout/menu_bg.png')] px-inner-section-x flex bg-center bg-no-repeat min-h-30 justify-center items-end max-w-1600 w-full m-auto">
    <div class="container mx-auto max-w-1290 w-full pb-6">
        <div class="flex justify-between items-end">

            <!-- Logo/Brand -->
            <div class="flex items-center">
                <a href="{{ route('home') }}"
                   class="">
                    <x-logo class="w-40 h-12 fill-[#5B8EAE]" />
                </a>
            </div>

            <!-- Navigation Links -->
            
            <x-other.top-menu/>

            <!-- Contact Links -->
            <div class="flex items-center space-x-4">
                <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors duration-200">
                    <x-icon.icon-switcher-color />
                </a>
                <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors duration-200">
                   <x-icon.icon-search />
                </a>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button"
                        class="text-gray-600 hover:text-[#60C0C3] focus:outline-none focus:text-[#60C0C3] transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>