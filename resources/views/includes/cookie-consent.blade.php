<div
    x-data
    x-transition:enter="transition duration-300 ease-out"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition duration-200 ease-in"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="p-inner-section-x fixed inset-x-0 bottom-0 z-50 mx-auto max-w-1290 pb-4"
>
    <div class="bg-background-dark flex flex-col items-center gap-4 px-6 py-5 sm:flex-row sm:gap-6">
        <div class="flex flex-col gap-2">
            <h4 class="text-text-dark">Этот сайт использует cookies</h4>
            <p class="text-text-dark text-medium flex-1 text-center sm:text-left">
                Мы используем файлы cookie для обеспечения максимального удобства, аналитики и персонализации контента. Продолжая
                использовать сайт, вы даете согласие на обработку персональных данных в соответствии с нашей
                <a href="/pd_agreement" class="hover:text-accent-add underline" target="_blank">Политикой обработки персональных данных</a>
                .
            </p>
        </div>

        <button
            @click="document.cookie = 'cookie_consent=1; path=/; max-age=' + 60*60*24*365; $el.closest('[x-data]').remove()"
            class="btn-primary group btn btn-primary-bg cursor-pointer no-underline focus:outline-none"
        >
            <div
                class="btn-primary-bg absolute top-0.75 -left-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
            ></div>

            <span class="btn-primary-text btn-text">Принять</span>

            <div
                class="btn-primary-bg absolute -right-0.75 bottom-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
            ></div>
        </button>
    </div>
</div>
