<div
    class="px-inner-section-x relative mx-auto flex min-h-74 max-w-1600 flex-col justify-end overflow-hidden bg-cover bg-right"
    style="
        background-image:
            linear-gradient(90deg, rgba(46, 72, 95, 1) 0%, rgba(46, 72, 95, 1) 36%, rgba(46, 72, 95, 0) 63%),
            url('{{ public_asset($imageUrl) }}');
    "
>
    <x-icon.icon-left-top-image class="absolute z-10 h-75 w-26 -translate-x-3.5 fill-white/70 md:-translate-x-8" />

    <div
        class="absolute top-0 left-0 h-full w-full bg-[url('/resources/images/layout/bg-mask-top-image.png')] bg-cover bg-center md:bg-left"
    ></div>

    <div class="bg-accent absolute bottom-0 left-0 z-10 h-1.5 w-8"></div>
    <div class="bg-accent absolute top-0 left-0 z-10 h-1.5 w-8"></div>

    <div class="mx-auto flex w-full max-w-1290 flex-row flex-wrap items-end justify-start gap-10 py-16">
        <img src="{{ public_asset($iconUrl) }}" class="z-10 hidden max-w-30 pb-2.5 lg:block" alt="{{ $iconAlt }}" />
        <a @if(!empty($url)) href="{{ $url }}" @endif class="z-10">
            <h1 class="@if(!empty($url)) hover:text-[#CBDEEA] @endif text-white">{!! $title !!}</h1>
        </a>
    </div>
</div>
