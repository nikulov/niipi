@props([
    'type' => 'white',
    'point' => '',
    'itemTitle' => 'Первый вопрос — заголовок аккордеона',
    'itemDescription' => '',
])

<button
    type="button"
    class="acc-{{ $type }} relative z-50 flex w-full cursor-pointer items-center justify-between gap-4 px-6 py-4"
    @click="open = !open"
>
    <div class="flex items-center justify-start gap-2 text-left">
        @if ($type == 'white')
            <span class="text-accordion-title text-accent-add min-w-15.5">{{ $point }}</span>
            <div class="icon-dots-{{ $type }} relative hidden h-6 w-5 bg-center bg-no-repeat md:block"></div>
        @endif

        <span class="text-accordion-title text-{{ $type }}">{{ $itemTitle }}</span>
    </div>

    <div class="flex items-center justify-end gap-4">
        <p class="text-normal hidden text-[#A7C1D2] md:block">{{ $itemDescription }}</p>
        <div class="acc-dots-{{ $type }} hidden h-6 w-38 bg-center bg-no-repeat md:block"></div>
        <div
            class="before:transform-all before:acc-arrow-white after:transform-all after:acc-arrow-white relative h-4 w-8 before:absolute before:inset-0 before:z-10 before:bg-center before:bg-no-repeat before:duration-400 before:content-[''] after:absolute after:inset-0 after:z-10 after:rotate-180 after:bg-center after:bg-no-repeat after:opacity-0 after:delay-200 after:duration-400 after:content-['']"
            :class="open ? 'before:opacity-0 before:translate-y-6 after:opacity-100 after:translate-y-0' : 'before:delay-500 after:translate-y-4'"
        ></div>
    </div>
</button>
