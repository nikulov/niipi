<div
        x-data="initToTopButton"
        x-init="init()"
        x-show="visible"
        x-transition.opacity
        class="fixed bottom-6 z-50 to-top right-2 2xl:right-[calc((100vw-1600px)/2)]"
>
    <button @click="scrollTop" id="to-top-btn" class="rounded-full cursor-pointer">
        <x-icon.icon-to-top class="w-16 h-16 transition-colors duration-300 ease-in-out"/>
    </button>
</div>
