<footer class="bg-[url('/resources/images/layout/bg_footer.jpg')] flex bg-cover bg-center bg-no-repeat lg:min-h-32 justify-center items-end dark:bg-[url('/resources/images/layout/bg_footer.jpg')] bg-[#5B8EAE] dark:bg-[#2F4A5F] bg-blend-multiply max-w-1600 w-full m-auto to-top-white">
    <div class="w-[1267px] mx-auto lg:px-0 lg:py-14">
        <div class="hidden lg:flex flex-col gap-10 lg:flex-row lg:items-start lg:justify-between">
            
            <div class="lg:w-1/4 flex items-center lg:items-start">
                <a href="{{ route('home') }}" class="inline-block">
                    <x-logo.logo class="w-40 h-auto fill-white dark:fill-white-dark" />
                </a>
            </div>
            
            <div class="lg:w-1/3">
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white dark:text-text-dark-dark whitespace-normal">
                    НАШИ КОНТАКТЫ
                </h3>
                
                <div class="space-y-1 text-sm leading-relaxed text-white dark:text-text-dark-dark rich-editor [&_p]:indent-0">
                    {!! $footer['contactData'] !!}
                </div>
            </div>
            
            <div class="lg:w-1/2">
                
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white dark:text-text-dark-dark whitespace-normal">
                    НАВИГАЦИЯ ПО САЙТУ
                </h3>
                
                <x-menu.desktop-footer/>
                
            </div>
            
            <div class="lg:w-1/4">
                
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white dark:text-text-dark-dark whitespace-normal">
                    СОЦИАЛЬНЫЕ СЕТИ
                </h3>
                
                <div class="flex items-center gap-3">
                    @foreach($footer['socialData'] as $item)
                        <x-other.social-icon url="{{$item['url']}}" icon-url="{{$item['iconUrl']}}"/>
                    @endforeach
                </div>
                
            </div>
        
        </div>
        
        <x-other.footer-mobile/>
        
    </div>
</footer>