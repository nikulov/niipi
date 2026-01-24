<nav
    class="dark:bg-background-dark px-inner-section-x m-auto flex min-h-30 w-full max-w-1600 items-end justify-center bg-white bg-[url('/resources/images/layout/menu_bg.png')] bg-center bg-no-repeat bg-blend-multiply"
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
                <button type="button" x-data="themeToggle" @click="toggle" class="cursor-pointer">
                    <x-icon.icon-switcher-theme />
                </button>
            </div>
        </div>
    </div>
</nav>
