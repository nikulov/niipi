@props([
    'title' => 'ЗАЧЕМ НУЖЕН ПРОЕКТ ГЕНПЛАНА?',
    'description' => 'для изменения границ населённых пунктов и функционального зонирования территории, а также необходим для изменения категории земельного участка',
    'btnUrl' => '#',
    'btnText' => 'ЗАКАЗАТЬ ПРОЕКТ',
])


    <section class="relative max-w-1242 w-full mx-auto my-inner-section-y px-inner-section-x">
        <div class="flex flex-row flex-wrap md:flex-nowrap gap-10">
            
            <div class="w-full md:w-3/4">
                <h2 class="mb-after-title text-primary dark:text-accent-add-dark">{{$title}}</h2>
                <div class="rich-editor text-normal dark:text-text-dark">
                    {!!$description!!}
                </div>
            </div>
            
            <div class="w-full md:w-1/4 flex justify-center md:justify-end items-center">
                <x-buttons.btn url="{{$btnUrl}}" text="{{$btnText}}" type="btn-accent"/>
            </div>
        
        </div>
    </section>

