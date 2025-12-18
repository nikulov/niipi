@props(
    [
        'url' => 'images/Genplan_header21.png',
        'alt' => 'alt'
    ]
)

<img src="{{ public_asset($url) }}" alt="{{ $alt }}" class="w-full max-w-1242 mx-auto my-inner-section-y px-inner-section-x">
