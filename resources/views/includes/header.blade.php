<nav
    class="dark:bg-background-dark px-inner-section-x mx-auto flex min-h-30 w-full max-w-1600 items-end justify-center bg-white bg-[url('/resources/images/layout/menu_bg.png')] bg-center bg-no-repeat bg-blend-multiply"
>
    <div class="container mx-auto w-full max-w-1290 pb-6">
        <div class="relative flex items-end justify-end lg:justify-center">
            <div class="absolute left-0 flex items-center">
                <a href="{{ route('home') }}" class="">
                    <x-logo class="dark:fill-accent-add h-12 w-40 fill-[#5B8EAE]" />
                </a>
            </div>

            <x-menu.top />

            <div class="absolute right-10 flex items-center space-x-4 lg:right-0">
                <button type="button" x-data="themeToggle" @click="toggle" class="group cursor-pointer">
                    <x-icon.icon-switcher-theme />
                    <div
                        class="text-white-dark dark:text-primary absolute bottom-full left-1/2 mb-2.5 w-38 -translate-x-32.5 rounded bg-[#2e3445] px-3 py-1 text-xs opacity-0 transition-opacity duration-200 group-hover:opacity-100 dark:bg-[#EFF0F2]"
                    >
                        {{ __('изменить тему сайта') }}
                    </div>
                </button>
            </div>
        </div>
    </div>
</nav>
