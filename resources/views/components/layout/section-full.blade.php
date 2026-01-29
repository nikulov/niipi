@props([
    'bgImageUrl' => '/resources/images/layout/bg-news.png',
])

<section
    class="py-inner-section-y px-inner-section-x bg-primary dark:bg-background-dark w-full bg-cover bg-center bg-no-repeat bg-blend-multiply"
    style="background-image: url('{{ public_asset("$bgImageUrl") }}')"
>
    <div class="m-auto flex max-w-1290 flex-col gap-10">
        {{ $slot }}
    </div>
</section>
