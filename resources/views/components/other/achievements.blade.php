@props([
    'amount' => '50',
    'text' => 'лет',
    'description' => 'Успешной работы в области градостроительства',
])

<div class="flex w-full flex-col gap-2 md:w-[270px]">
    <div class="border-primary dark:border-text-dark border-b">
        <span class="text-achievements-sec text-primary dark:text-text-dark">&gt;</span>
        <span class="text-achievements text-primary dark:text-text-dark">{{ $amount }}</span>
        <span class="text-achievements-sec text-primary dark:text-text-dark pl-2 text-xl">{{ $text }}</span>
    </div>
    <p class="text-big text-primary dark:text-text-dark pt-1 leading-5 tracking-[1px]">{{ $description }}</p>
</div>
