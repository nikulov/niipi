<div class="flex flex-col items-center justify-center lg:hidden">
    <x-menu.mobile-contact-block />

    <a href="#" class="mb-inner-section-y px-inner-section-x flex items-center justify-center gap-8">
        <x-icon.icon-point class="fill-accent-add dark:fill-accent-add-dark h-9 w-6" />
        <span class="text-small font-century dark:text-white-dark max-w-55 font-bold text-white">
            129110, г. Москва ул. Гиляровского, дом 47, строение 3.
        </span>
    </a>

    <hr class="dark:border-white-dark w-full border-white" />

    <span class="text-small font-century dark:text-white-dark mt-2 pb-2 text-center text-white">
        © {{ config('app.name') }} {{ $year }}
    </span>
</div>
