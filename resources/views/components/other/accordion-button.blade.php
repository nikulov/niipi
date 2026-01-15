@props([
    'type' => 'white',
    'point' => '',
    'itemTitle' => 'Первый вопрос — заголовок аккордеона',
    'itemDescription' => ''
])

<button
        type="button"
        class="relative w-full px-6 py-4 flex items-center justify-between gap-4 acc-{{$type}} cursor-pointer z-50"
        @click="open = !open"
>
    <div class="flex text-left justify-start items-center gap-2">
        @if($type == 'white')
        <span class=" text-accordion-title text-accent-add min-w-14">{{$point}}</span>
        <div class="hidden md:block w-5 h-6 relative icon-dots-{{$type}} bg-no-repeat bg-center"></div>
        @endif
        <span class="text-accordion-title text-{{$type}}">{{$itemTitle}}</span>
    </div>
    
    <div class="flex justify-end items-center gap-4">
        <p class="hidden md:block text-normal text-[#A7C1D2]">{{$itemDescription}}</p>
        <div class="hidden md:block w-38 h-6 acc-dots-{{$type}} bg-no-repeat bg-center"></div>
        <div class="w-8 h-4 relative
                    before:content-[''] before:absolute before:inset-0 before:z-10 before:transform-all before:duration-400
                    before:acc-arrow-white
                    before:bg-no-repeat before:bg-center
                    after:content-[''] after:absolute after:inset-0 after:z-10 after:transform-all after:duration-400
                    after:acc-arrow-white
                    after:bg-no-repeat after:bg-center after:rotate-180 after:opacity-0 after:delay-200"
             :class="open ? 'before:opacity-0 before:translate-y-6 after:opacity-100 after:translate-y-0' : 'before:delay-500 after:translate-y-4'">
        </div>
    </div>
</button>
