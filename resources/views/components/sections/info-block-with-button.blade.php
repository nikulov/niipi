@props([
    'title' => 'ЗАЧЕМ НУЖЕН ПРОЕКТ ГЕНПЛАНА?',
    'description' => 'для изменения границ населённых пунктов и функционального зонирования территории, а также необходим для изменения категории земельного участка',
    'btnUrl' => '#',
    'btnText' => 'ЗАКАЗАТЬ ПРОЕКТ',
])

<section class="my-inner-section-y px-inner-section-x relative mx-auto w-full max-w-1242">
    <div class="flex flex-row flex-wrap gap-10 md:flex-nowrap">
        <div class="w-full md:w-3/4">
            <h2 class="mb-after-title text-primary dark:text-accent-add-dark">
                {!! nl2br(e($title)) !!}
            </h2>
            <div class="rich-editor text-normal dark:text-text-dark">
                {!! $description !!}
            </div>
        </div>

        <div class="flex w-full items-center justify-center md:w-1/4 md:justify-end">
            <x-buttons.btn url="{{$btnUrl}}" text="{{$btnText}}" type="btn-accent" />
        </div>
    </div>
</section>
