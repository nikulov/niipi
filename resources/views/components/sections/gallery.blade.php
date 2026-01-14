@props([
    "urls" => [
        "gallery/posts/default/image-2-1766065371.jpg",
        "gallery/posts/default/image-3-1766065371.jpg",
        "gallery/posts/default/umg-1766065371.jpg",
        "gallery/posts/default/main-slider-01-v2-3-1766065371.jpg",
        "gallery/posts/default/standart-1766065371.jpg",
        "gallery/posts/default/gpzu-1766065371.jpg",
        "gallery/posts/default/kuzmina-aa-kazanysh-1-1766065371.jpeg"
      ]
])


<div x-data="{
                open: false,
                currentSrc: '',
            }"
     class="w-full max-w-1242 mx-auto my-inner-section-y px-inner-section-x">
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($urls as $url)
            <button
                    type="button"
                    class="relative block w-full aspect-4/3 overflow-hidden hover:opacity-80 transition cursor-pointer"
                    @click="
                                open = !open
                                currentSrc = @js(public_asset($url));
                            "
            >
                <img
                        src="{{ public_asset($url) }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                 alt="">
            </button>
        @endforeach
    </div>
    
    <div
            x-show="open"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 none"
            @click.self="open = !open"
    >
        <button
                type="button"
                class="absolute top-4 right-4 p-2 text-white text-2xl leading-none cursor-pointer"
                aria-label="Close image"
                @click="open = !open"
        >
            ×
        </button>
        
        <div class="max-w-5xl w-full px-4">
            <img
                    :src="currentSrc"
                    class="w-full h-auto max-h-[90vh] object-contain mx-auto"
             alt="">
        </div>
    </div>
</div>