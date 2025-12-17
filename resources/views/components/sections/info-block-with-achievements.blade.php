@props([
    'imageUrl' => '/images/layout/bg-info.png',
    'title' => '// ГАУ МО «НИиПИ градостроительства»',
    'description' => ' Государственное автономное учреждение Московской области «Научно-исследовательский и проектный
                        институт градостроительства» (ГАУ МО «НИиПИ градостроительства») образован 14 мая 1974 года.',
    'achievements' =>
    [
        [
            'quantity' => '50',
            'title' => 'лет',
            'description' => 'Успешной работы в области градостроительства',
        ],
        
         [
            'quantity' => '200',
            'title' => 'сотрудников',
            'description' => 'С высочайшим уровнем профессионализма',
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

<div class="w-full max-w-1290 bg-cover bg-center bg-no-repeat mx-auto"
     style="background-image: url('{{ public_asset("$imageUrl") }}');"
>
    <div class="max-w-1242 mx-auto px-inner-section-x p-inner-section-y flex flex-col gap-10 justify-start items-start">
        <div class="flex flex-row flex-wrap md:flex-nowrap gap-10">
            
            <div class="w-full md:w-1/2">
                
                <h2 class="mb-after-title text-primary">{{$title}}</h2>
                
                <div class="text-normal ">
                    {!!$description!!}
                </div>
            
            </div>
            
            <div class="w-full md:w-1/2 grid grid-cols-1 md:grid-cols-2 gap-10">
                
                @foreach($achievements as $achievement)
                    <x-other.achievements
                            amount="{{$achievement['quantity']}}"
                            text="{{$achievement['title']}}"
                            description="{!!$achievement['description']!!}"
                    />
                @endforeach
                
            </div>
            
        </div>
    </div>
</div>