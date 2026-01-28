@props([
    'btnLabel' => 'Кнопка',
    'btnUrl' => '#',
    'btnType' => 'btn-primary',
    'btnPosition' => 'end',
    'blank' => false,
])

<section class="px-inner-section-x my-inner-section-y justify-{{ $btnPosition }} container mx-auto flex max-w-1242 flex-row">
    <x-buttons.btn url="{{$btnUrl}}" text="{{$btnLabel}}" type="{{$btnType}}" blank="{{$blank}}" />
</section>
