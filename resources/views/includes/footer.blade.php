<footer
    class="to-top-white px-inner-section-x m-auto flex w-full max-w-1600 items-end justify-center bg-[#5B8EAE] bg-[url('/resources/images/layout/bg_footer.jpg')] bg-cover bg-center bg-no-repeat bg-blend-multiply lg:min-h-32 dark:bg-[#2F4A5F] dark:bg-[url('/resources/images/layout/bg_footer.jpg')]"
>
    <div class="mx-auto w-full max-w-1290 lg:px-0 lg:py-14">
        <div class="hidden flex-col gap-10 lg:flex lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-center lg:w-1/4 lg:items-start">
                <a href="{{ route('home') }}" class="inline-block">
                    <x-logo.logo class="dark:fill-white-dark h-auto w-40 fill-white" />
                </a>
            </div>

            <div class="lg:w-1/3">
                <h3 class="dark:text-white-dark mb-4 text-sm font-bold tracking-[0.18em] whitespace-normal text-white uppercase">
                    НАШИ КОНТАКТЫ
                </h3>

                <div class="dark:text-white-dark rich-editor space-y-1 text-sm leading-relaxed text-white [&_p]:indent-0">
                    {!! $footer['contactData'] !!}
                </div>
            </div>

            <div class="lg:w-1/2">
                <h3 class="dark:text-white-dark mb-4 text-sm font-bold tracking-[0.18em] whitespace-normal text-white uppercase">
                    НАВИГАЦИЯ ПО САЙТУ
                </h3>

                <x-menu.desktop-footer />
            </div>

            <div class="max-w-43.75 lg:w-1/4">
                <h3 class="dark:text-white-dark mb-4 text-sm font-bold tracking-[0.18em] whitespace-normal text-white uppercase">
                    СОЦИАЛЬНЫЕ СЕТИ
                </h3>

                <div class="flex items-center justify-between gap-3">
                    @foreach ($footer['socialData'] as $item)
                        <x-other.social-icon url="{{$item['url']}}" icon-url="{{$item['iconUrl']}}" />
                    @endforeach
                </div>
            </div>
        </div>

        <x-other.footer-mobile />
    </div>
</footer>
