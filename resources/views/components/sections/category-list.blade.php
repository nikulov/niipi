@php($basePath = request()->segment(1))

<div class="w-full max-w-1242 mx-auto my-inner-section-y px-inner-section-x">
    <div class="w-full border-t border-accent pb-2"></div>
    <span class="pr-1 text-accent text-small uppercase font-bold">Категории: </span>
    
    @foreach($categories as $slug => $category)
        <a  href="{{ '/' . $basePath . '?' . $basePath .'Category=' . urlencode($slug) }}"
            class="text-accent hover:text-accent-add text-small underline"
        >
            {{$category}}
        </a>
        @if(! $loop->last)<span class="text-accent text-small">, </span> @endif
    @endforeach
    
</div>