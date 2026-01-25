@props([
    "menuItems" => [],
])

<div
    class="relative flex h-auto w-full items-end"
    x-data="{
        menuOpen: false,
        scrollY: 0,
        init() {
            this.$watch('menuOpen', (v) => {
                if (v) {
                    this.scrollY = window.scrollY

                    document.body.style.position = 'fixed'
                    document.body.style.top = `-${this.scrollY}px`
                    document.body.style.left = '0'
                    document.body.style.right = '0'
                    document.body.style.width = '100%'
                    document.body.style.overflow = 'hidden'
                } else {
                    document.body.style.position = ''
                    document.body.style.top = ''
                    document.body.style.left = ''
                    document.body.style.right = ''
                    document.body.style.width = ''
                    document.body.style.overflow = ''

                    window.scrollTo(0, this.scrollY)
                }
            })
        },
    }"
    @keydown.escape.window="menuOpen = false"
>
    <button
        type="button"
        class="text-accent hover:text-accent-add cursor-pointer transition-colors duration-200 focus:outline-none"
        @click="menuOpen = true"
    >
        <x-icon.icon-arrow-down class="fill-accent dark:fill-accent-add-dark h-6 w-6" />
    </button>

    <div
        x-cloak
        x-show="menuOpen"
        x-transition:enter="transition duration-400 ease-out"
        x-transition:enter-start="-translate-y-full"
        x-transition:enter-end="translate-y-0 "
        x-transition:leave="transition duration-300 ease-in"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="-translate-y-full"
        class="dark:bg-background-dark bg-accent fixed inset-0 z-50 transform touch-pan-y overflow-y-auto overscroll-contain bg-[url('/resources/images/layout/bg-mobile-menu.png')] bg-cover bg-center bg-blend-multiply"
    >
        <div class="px-inner-section-x flex flex-row items-end justify-between py-12">
            <div class="flex items-center">
                <a href="{{ route("home") }}" class="">
                    <x-logo class="dark:fill-accent-add-dark h-12 w-40 fill-white" />
                </a>
            </div>

            <button
                type="button"
                class="hover:text-accent-add translate-y-20 cursor-pointer text-white opacity-0 transition-all delay-1000 duration-150 focus:outline-none"
                @click="menuOpen = false"
                x-transition
                :class="menuOpen ? 'opacity-100 translate-y-0! pointer-events-auto' : ''"
            >
                <x-icon.icon-arrow-down class="dark:fill-accent-add-dark h-6 w-6 rotate-180 fill-white" />
            </button>
        </div>

        <div class="flex flex-col justify-center">
            <div class="flex items-center justify-center space-x-8 bg-white/60">
                <ul class="px-inner-section-x flex w-full flex-col items-center justify-center space-x-6" x-data="{ openIndex: null }">
                    @foreach ($menuItems as $i => $item)
                        @php($hasChildren = ! empty($item["children"]))

                        <li
                            class="group relative m-0 flex w-full flex-row items-center justify-center border-b border-white last:border-0"
                            @if ($hasChildren)
                                @click="openIndex = openIndex === {{ $i }} ? null : {{ $i }}"
                            @endif
                            :class="openIndex === {{ $i }} ? 'border-b-0' : ''"
                        >
                            <x-menu.mobile-link href="{{$item['href']}}" blank="{{$item['blank']}}">
                                {{ $item["label"] }}
                            </x-menu.mobile-link>
                        </li>

                        @if ($hasChildren)
                            <ul
                                class="px-inner-section-x mr-0 flex w-full flex-col items-center justify-center border-white"
                                x-cloak
                                x-show="openIndex === {{ $i }}"
                                x-collapse.duration.100ms
                                x-transition:enter="transition duration-100 ease-out"
                                x-transition:enter-start="border-b-0 opacity-0"
                                x-transition:enter-end="border-b opacity-100"
                                x-transition:leave="transition duration-100 ease-in"
                                x-transition:leave-start="border-b-0 opacity-100"
                                x-transition:leave-end="border-b-0 opacity-0"
                                :class="openIndex === {{ $i }}  ? 'border-b' : 'border-b-0'"
                            >
                                @foreach ($item["children"] as $child)
                                    <li class="inline-block w-full last:pb-6">
                                        <x-menu.mobile-link
                                            href="{{ $child['href'] }}"
                                            blank="{{ $child['blank'] }}"
                                            class="py-4 normal-case"
                                        >
                                            {{ $child["label"] }}
                                        </x-menu.mobile-link>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach
                </ul>
            </div>

            <x-other.footer-mobile />
        </div>
    </div>
</div>
