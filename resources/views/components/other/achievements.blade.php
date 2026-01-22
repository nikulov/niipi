@props([
    'amount' => '50',
    'text' => 'лет',
    'description' => 'Успешной работы в области градостроительства'
])

<div class="flex flex-col gap-2 w-full md:w-[270px]">
    <div class="border-b border-primary dark:border-text-dark">
        <span class="text-achievements-sec text-primary dark:text-text-dark">&gt; </span>
        <span class="text-achievements text-primary dark:text-text-dark">{{$amount}}</span>
        <span class="pl-2 text-achievements-sec text-primary dark:text-text-dark text-xl">{{$text}}</span>
    </div>
    <p class="text-big pt-1 text-primary dark:text-text-dark leading-5 tracking-[1px]">{{$description}}</p>
</div>
