@props([
    'imageUrl' => '/images/layout/bg-info.png',
    'title' => '// ГАУ МО «НИиПИ градостроительства»',
    'description' => ' Государственное автономное учреждение Московской области «Научно-исследовательский и проектный
                        институт градостроительства» (ГАУ МО «НИиПИ градостроительства») образован 14 мая 1974 года.',
    'achievements' => [
        [
            'quantity' => '50',
            'title' => 'лет',
            'description' => 'Успешной работы в области градостроительства',
        ],

        [
            'quantity' => '200',
            'title' => 'сотрудников',
            'description' => 'С высочайшим уровнем профессионализма',
        ],

        [
            'quantity' => '5000',
            'title' => 'проектов',
            'description' => 'В градостроительной сфере',
        ],

        [
            'quantity' => '20',
            'title' => 'регионов',
            'description' => 'География проектов',
        ],
    ],
])

<section class="mx-auto w-full max-w-1290 bg-cover bg-right bg-no-repeat" style="background-image: url('{{ public_asset("$imageUrl") }}')">
    <div class="px-inner-section-x p-inner-section-y mx-auto flex max-w-1242 flex-col items-start justify-start gap-10">
        <div class="flex flex-row flex-wrap gap-10 xl:flex-nowrap">
            <div class="w-full xl:w-1/2">
                <h2 class="mb-after-title text-primary dark:text-accent-add-dark">
                    {!! nl2br(e($title)) !!}
                </h2>

                <div class="rich-editor text-normal dark:text-text-dark">
                    {!! $description !!}
                </div>
            </div>

            <div class="mx-auto grid w-full grid-cols-1 gap-9.25 md:w-193.25 md:grid-cols-2 xl:w-1/2">
                @foreach ($achievements as $achievement)
                    <x-other.achievements
                        amount="{{$achievement['quantity']}}"
                        text="{{$achievement['title']}}"
                        description="{!!$achievement['description']!!}"
                    />
                @endforeach
            </div>
        </div>
    </div>
</section>
