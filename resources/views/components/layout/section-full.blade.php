@props([
    'bgImageUrl' => '/resources/images/layout/bg-news.png'
])

<div class="py-inner-section-y px-inner-section-x w-full bg-cover bg-center bg-no-repeat bg-primary"
     style="background-image: url('{{public_asset("$bgImageUrl") }}')">
    <div class="flex flex-col gap-10 m-auto max-w-1290">
      {{ $slot }}
    </div>
</div>