<div
        x-data="initToTopButton"
        x-init="init()"
        x-show="visible"
        x-transition.opacity
        class="fixed bottom-6 right-2 z-50 to-top"
>
    <button @click="scrollTop" id="to-top-btn" class="rounded-full cursor-pointer">
        <x-icon.icon-to-top class="w-16 h-16 transition-colors duration-300 ease-in-out"/>
    </button>
</div>
