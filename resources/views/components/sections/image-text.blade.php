@props([
    'title' => '',
    'subtitle' => '',
    'url' => 'images/image 2.jpg',
    'alt' => 'alt',
    'position' => '1',
    'content' => '27 ноября 2025 года на площадке НИУ МГСУ начал свою работу Седьмой  Объединенный Евразийский конгресс «ТИМ-сообщество. Люди. Технологии.  Стратегия». <br> <br>
        Главный градостроитель нашего Института Надежда Николаевна Зыкова приняла  участие в работе сессии «Информационное моделирование в  градостроительной деятельности и развитие ГИСОГД».<br> <br>
        Сессия прошла при поддержке Оргкомитета Совета главных архитекторов им. А.В. Кузьмина Союза архитекторов России.<br> <br>
        В своем докладе «Цифровое моделирование как инструмент комплексного  развития территории» спикер подчеркнула важность интеграции  инновационных методов цифрового проектирования и анализа  пространственных данных для принятия обоснованных решений в  градостроительной сфере, а также обратила внимание на возрастающую роль  информационных моделей в процессе планирования городской среды.',
])

<section class="my-inner-section-y px-inner-section-x mx-auto w-full max-w-1242">
    @if ($title)
        <h2 class="text-primary dark:text-accent-add-dark">{{ $title }}</h2>
    @endif

    @if ($subtitle)
        <h3 class="text-text dark:text-white-dark mb-8">{{ $subtitle }}</h3>
    @endif

    <div class="grid w-full grid-cols-1 items-start gap-12 md:grid-cols-3">
        <div class="flex h-full max-h-75 items-center md:col-span-1" style="order: {{ $position }}">
            <img src="{{ public_asset($url) }}" alt="{{ $alt }}" class="h-full w-full object-cover" />
        </div>

        <div class="rich-editor text-normal text-text dark:text-text-dark order-1 text-left md:col-span-2">
            {!! $content !!}
        </div>
    </div>
</section>
