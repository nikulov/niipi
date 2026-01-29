@props([
    'url' => 'images/Genplan_header21.png',
    'alt' => 'alt',
])
<section class="my-inner-section-y px-inner-section-x mx-auto w-full max-w-1242">
    <img src="{{ public_asset($url) }}" alt="{{ $alt }}" class="mx-auto w-full" />
</section>
