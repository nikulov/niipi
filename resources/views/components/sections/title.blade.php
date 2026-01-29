@props([
    'type' => 'h2',
    'position' => 'left',
    'title' => 'Надежда Зыкова приняла участие в работе сессии «Информационное моделирование в градостроительной деятельности и развитие ГИСОГД»',
])

<{{ $type }}
    class="mt-inner-section-y px-inner-section-x text-primary dark:text-accent-add-dark mx-auto w-full max-w-1242"
    style="text-align: {{ $position }}"
>
    {!! nl2br(e($title)) !!}
</{{ $type }}>
