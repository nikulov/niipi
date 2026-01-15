@props([
    'amount' => '50',
    'text' => 'лет',
    'description' => 'Успешной работы в области градостроительства'
])

<div class="flex flex-col gap-2 w-full md:w-[270px]">
    <div class="border-b border-primary">
        <span class="text-achievements-sec text-primary">&gt; </span>
        <span class="text-achievements text-primary">{{$amount}}</span>
        <span class="pl-2 text-achievements-sec text-primary text-xl">{{$text}}</span>
    </div>
    <p class="text-big pt-1 text-primary leading-5 tracking-[1px]">{{$description}}</p>
</div>
