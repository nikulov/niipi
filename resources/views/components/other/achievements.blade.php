@props([
    'amount' => '50',
    'text' => 'лет',
    'description' => 'Успешной работы в области градостроительства'
])

<div class="flex flex-col gap-2">
    <div class="border-b border-primary pb-2 min-w-[270px]">
        <span class="text-achievements-sec text-primary">&gt; </span>
        <span class="text-achievements text-primary">{{$amount}}</span>
        <span class="text-achievements-sec text-primary">{{$text}}</span>
    </div>
    <p class="text-big text-primary">{{$description}}</p>
</div>
