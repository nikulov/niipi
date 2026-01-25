@props([
    'btnLabel' => 'Кнопка',
    'btnUrl' => '#',
    'btnType' => 'btn-primary',
    'btnPosition' => 'end',
    'blank' => false,
])

<div class="px-inner-section-x py-inner-section-y justify-{{ $btnPosition }} flex flex-row">
    <x-buttons.btn url="{{$btnUrl}}" text="{{$btnLabel}}" type="{{$btnType}}" blank="{{$blank}}" />
</div>
