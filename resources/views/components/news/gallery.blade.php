@props([
    'images' => [
        [
            'url' => 'images/content/Zykova-1-500x39.png',
            'alt' => 'alt',
        ],
        [
            'url' => 'images/content/Zykova-1-500x39.png',
            'alt' => 'alt',
        ],
        [
            'url' => 'images/content/Zykova-1-500x39.png',
            'alt' => 'alt',
        ],
        [
            'url' => 'images/content/Zykova-1-500x39.png',
            'alt' => 'alt',
        ],
    ],
])


<div x-data="imageGalleryModal" class="w-full max-w-1242 mx-auto my-inner-section-y px-inner-section-x">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($images as $image)
            <button
                    type="button"
                    class="relative block w-full aspect-4/3 overflow-hidden hover:opacity-80 transition"
                    @click="show(@js($image['url']), @js($image['alt']))"
            >
                <img
                        src="{{ $image['url'] }}"
                        alt="{{ $image['alt'] }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                >
            </button>
        @endforeach
    </div>
    
    <div
            x-show="isOpen"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
            @click.self="close()"
    >
        <button
                type="button"
                class="absolute top-4 right-4 p-2 text-white text-2xl leading-none"
                aria-label="Close image"
                @click="close()"
        >
            ×
        </button>
        
        <div class="max-w-5xl w-full px-4">
            <img
                    :src="currentSrc"
                    :alt="currentAlt"
                    class="w-full h-auto max-h-[90vh] object-contain mx-auto"
            >
        </div>
    </div>
</div>