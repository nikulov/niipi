<div
    x-data="initToTopButton"
    x-init="init()"
    x-show="visible"
    x-transition.opacity
    class="to-top fixed right-2 bottom-6 z-50 2xl:right-[calc((100vw-1600px)/2)]"
>
    <button @click="scrollTop" id="to-top-btn" class="cursor-pointer rounded-full">
        <x-icon.icon-to-top class="dark:text-text-dark h-16 w-16 transition-colors duration-300 ease-in-out" />
    </button>
</div>
