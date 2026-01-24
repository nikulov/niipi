@props([
    'menuItems' => [],
])

<div class="hidden items-center space-x-8 md:flex">
    <ul class="flex space-x-6">
        @foreach ($menuItems as $item)
            @php($hasChildren = ! empty($item['children']))

            <li
                class="group after:text-accent dark:after:text-accent-add-dark after: relative mr-4 flex flex-row items-center after:ml-4 after:content-['//'] last:after:hidden"
            >
                <x-menu.desktop-link href="{{$item['href']}}" blank="{{$item['blank']}}">
                    {{ $item['label'] }}
                </x-menu.desktop-link>

                @if ($hasChildren)
                    <x-menu.desktop-childe-block :items="$item['children']" />
                @endif
            </li>
        @endforeach
    </ul>
</div>
