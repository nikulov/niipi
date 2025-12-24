<footer class="bg-[url('/resources/images/layout/bg_footer.jpg')] flex bg-cover bg-center bg-no-repeat min-h-32 justify-center items-end dark:bg-[url('/resources/images/layout/bg_footer.jpg')] bg-[#5B8EAE] bg-blend-multiply max-w-1600 w-full m-auto">
    <div class="w-[1267px] mx-auto px-4 lg:px-0 py-10 lg:py-14">
        <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:justify-between">
            
            <div class="lg:w-1/4 flex items-center lg:items-start">
                <a href="{{ route('home') }}" class="inline-block">
                    <x-logo.logo class="w-40 h-auto fill-white" />
                </a>
            </div>
            
            <div class="lg:w-1/3">
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white whitespace-normal">
                    НАШИ КОНТАКТЫ
                </h3>
                
                <div class="space-y-1 text-sm leading-relaxed text-white rich-editor">
                    {!! $footer['contactData'] !!}
                </div>
            </div>
            
            <div class="lg:w-1/2">
                
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white whitespace-normal">
                    НАВИГАЦИЯ ПО САЙТУ
                </h3>
                
                <x-menu.desktop-footer/>
                
            </div>
            
            <div class="lg:w-1/4">
                
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white whitespace-normal">
                    СОЦИАЛЬНЫЕ СЕТИ
                </h3>
                
                <div class="flex items-center gap-3">
                    @foreach($footer['socialData'] as $item)
                        <x-other.social-icon url="{{$item['url']}}" icon-url="{{$item['iconUrl']}}"/>
                    @endforeach
                </div>
                
            </div>
        
        </div>
    </div>
</footer>