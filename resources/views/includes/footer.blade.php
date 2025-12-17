<footer class="bg-[url('/resources/images/layout/bg_footer.jpg')] flex bg-cover bg-center bg-no-repeat min-h-32 justify-center items-end dark:bg-[url('/resources/images/layout/bg_footer.jpg')] bg-[#5B8EAE] bg-blend-multiply max-w-1600 w-full m-auto">
    <div class="w-[1267px] mx-auto px-4 lg:px-0 py-10 lg:py-14">
        <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:justify-between">
            
            <div class="lg:w-1/4 flex items-center lg:items-start">
                <a href="{{ route('home') }}" class="inline-block">
                    <x-logo.logo class="w-40 h-auto fill-white" />
                </a>
            </div>
            
            <div class="lg:w-1/3">
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white">
                    НАШИ КОНТАКТЫ
                </h3>
                
                <div class="space-y-1 text-sm leading-relaxed text-white">
                    <p>129110, г. Москва</p>
                    <p>ул. Гиляровского, д.47, стр.3</p>
                    <p>Режим работы с 9:00 – 18:00</p>
                    <p>8 (495) 242-77-07</p>
                    <p>niipigrad_niipi@mosreg.ru</p>
                </div>
            </div>
            
            <div class="lg:w-1/2">
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white">
                    НАВИГАЦИЯ ПО САЙТУ
                </h3>
                
                <div class="grid grid-cols-1 gap-y-1 gap-x-10 md:grid-cols-2 text-sm leading-relaxed text-white">
                    <ul class="space-y-1 list-disc list-inside">
                        <li><a href="#" class="hover:underline">Институт</a></li>
                        <li><a href="#" class="hover:underline">Структура</a></li>
                        <li><a href="#" class="hover:underline">Новости института</a></li>
                        <li><a href="#" class="hover:underline">Портфолио</a></li>
                        <li><a href="#" class="hover:underline">Порталы Госуслуг</a></li>
                    </ul>
                    
                    <ul class="space-y-1 list-disc list-inside ">
                        <li><a href="#" class="hover:underline">Заказчикам</a></li>
                        <li><a href="#" class="hover:underline">Перечень услуг</a></li>
                        <li><a href="#" class="hover:underline">Наши лицензии</a></li>
                        <li><a href="#" class="hover:underline">Закупки</a></li>
                        <li><a href="#" class="hover:underline">Порталы Подмосковья</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="lg:w-1/4">
                <h3 class="mb-4 text-sm font-bold tracking-[0.18em] uppercase text-white">
                    СОЦИАЛЬНЫЕ СЕТИ
                </h3>
                
                <div class="flex items-center gap-3">
                    <a href="#" class="flex h-10 w-10 items-center justify-center">
                        <x-logo.logo-rutube class="fill-white" />
                    </a>
                    <a href="#" class="flex h-10 w-10 items-center justify-center">
                        <x-logo.logo-tg class="fill-white"/>
                    </a>
                    <a href="#" class="flex h-10 w-10 items-center justify-center">
                        <x-logo.logo-vk class="fill-white"/>
                    </a>
                </div>
            </div>
        
        </div>
    </div>
</footer>