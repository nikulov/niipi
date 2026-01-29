@php($basePath = request()->segment(1))

<section class="my-inner-section-y px-inner-section-x mx-auto w-full max-w-1242">
    <div class="border-accent w-full border-t pb-2"></div>
    <span class="text-accent text-medium pr-1 font-bold uppercase">Категории:</span>

    @foreach ($categories as $slug => $category)
        <a
            href="{{ '/' . $basePath . '?' . $basePath . 'Category=' . urlencode($slug) }}"
            class="text-accent hover:text-accent-add text-medium underline"
        >
            {{ $category }}
        </a>
        @if (! $loop->last)
            <span class="text-accent text-medium">,</span>
        @endif
    @endforeach
</section>
