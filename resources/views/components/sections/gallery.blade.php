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

<div
        x-data="{
                    open: false,
                    startIndex: 0,
                    openAt(i) {
                        this.startIndex = i;
                        this.open = true;
                        
                        this.$nextTick(() => {
                        window.initGallerySliderForRoot(this.$refs.modalRoot, this.startIndex);
                        });
                    },
                    close() {this.open = false;}
                }"
        class="w-full max-w-1242 mx-auto my-inner-section-y px-inner-section-x"
>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($urls as $i => $url)
            <button
                    type="button"
                    class="relative block w-full aspect-4/3 overflow-hidden hover:opacity-80 transition cursor-pointer"
                    @click="openAt({{$i}})"
            >
                <img
                        src="{{public_asset($url)}}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                        alt=""
                >
            </button>
        @endforeach
    </div>
    
    <div
            x-show="open"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
            @click.self="close()"
            @keydown.escape.window="close()"
    >
        <button
                type="button"
                class="absolute top-4 right-4 z-30 p-2 text-white text-2xl leading-none cursor-pointer"
                aria-label="Закрыть галерею"
                @click="close()"
        >
            ×
        </button>
        
        <div
                x-ref="modalRoot"
                class="relative w-full max-w-1600 mx-auto px-4 flex flex-col items-center"
        >
            <!-- Main -->
            <div
                    class="swiper js-gallery-slider w-full max-w-5xl h-[min(80vh,700px)] max-lg:h-[min(70vh,560px)] max-sm:h-[min(60vh,420px)]"
                    style="--swiper-navigation-color:#fff;--swiper-pagination-color:#fff;"
            >
                <div class="swiper-wrapper h-full">
                    @foreach($urls as $url)
                        <div class="swiper-slide h-full flex items-center justify-center">
                            <img
                                    src="{{ public_asset($url) }}"
                                    class="w-full h-full max-h-full object-contain"
                                    alt=""
                            >
                        </div>
                    @endforeach
                </div>
                
                <button type="button"
                        class="js-slider-prev js-gallery-slider-prev absolute left-0 top-1/2 -translate-y-1/2 cursor-pointer w-10 h-10 z-20"
                ></button>
                <button type="button"
                        class="js-slider-next js-gallery-slider-next absolute right-0 top-1/2 -translate-y-1/2 cursor-pointer w-10 h-10 z-20"
                ></button>
                
                <div class="absolute bottom-2 left-0 right-0 flex justify-center z-20">
                    <div class="js-gallery-slider-pagination"></div>
                </div>
            </div>
            
            <!-- Thumbs -->
            <div class="swiper js-gallery-slider-thumbs w-full max-w-5xl mt-6 h-[142px] max-sm:h-[96px]">
                <div class="swiper-wrapper h-full">
                    @foreach($urls as $url)
                        <div class="swiper-slide !w-[200px] !h-[142px] max-sm:!w-[120px] max-sm:!h-[96px] opacity-70 cursor-pointer">
                            <img
                                    src="{{ public_asset($url) }}"
                                    class="w-full h-full object-cover"
                                    alt=""
                            >
                        </div>
                    @endforeach
                </div>
            </div>
        
        </div>
    </div>
</div>