@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet =
        $scrollTo !== false
            ? <<<JS
               (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
            JS
            : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            <div class="flex flex-1 justify-center gap-x-8 sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span
                            class="br-transparent relative inline-flex cursor-default items-center px-0 py-2 text-sm leading-5 font-medium text-[#A7C1D2] uppercase"
                        >
                            {!! __('pagination.previous') !!}
                        </span>
                    @else
                        <button
                            type="button"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                            class="text-accent br-transparent hover:text-accent-add relative inline-flex cursor-pointer items-center px-0 py-2 text-sm leading-5 font-medium uppercase transition duration-150 ease-in-out"
                        >
                            {!! __('pagination.previous') !!}
                        </button>
                    @endif
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        <button
                            type="button"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                            class="text-accent br-transparent hover:text-accent-add relative ml-3 inline-flex cursor-pointer items-center px-0 py-2 text-sm leading-5 font-medium uppercase transition duration-150 ease-in-out"
                        >
                            {!! __('pagination.next') !!}
                        </button>
                    @else
                        <span
                            class="br-transparent relative ml-3 inline-flex cursor-default items-center px-0 py-2 text-sm leading-5 font-medium text-[#A7C1D2] uppercase"
                        >
                            {!! __('pagination.next') !!}
                        </span>
                    @endif
                </span>
            </div>

            <div class="hidden sm:flex sm:flex-1 sm:flex-col sm:items-center sm:justify-center">
                <div>
                    <p class="text-accent text-sm leading-5">
                        <span>{!! __('pagination.showing') !!}</span>
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        <span>{!! __('pagination.to') !!}</span>
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                        <span>{!! __('pagination.of') !!}</span>
                        <span class="font-medium">{{ $paginator->total() }}</span>
                    </p>
                </div>

                <div>
                    <span class="relative z-0 inline-flex items-center rtl:flex-row-reverse">
                        <span>
                            {{-- Previous Page Link --}}

                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                    <span
                                        class="br-transparent relative inline-flex cursor-default items-center px-2 py-2 text-sm leading-5 font-medium text-[#A7C1D2]"
                                        aria-hidden="true"
                                    >
                                        <svg class="h-7.5 w-7.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                                    class="text-accent br-transparent hover:text-accent-add active:text-primary relative inline-flex cursor-pointer items-center px-2 py-2 text-sm leading-5 font-medium transition duration-150 ease-in-out"
                                    aria-label="{{ __('pagination.previous') }}"
                                >
                                    <svg class="h-7.5 w-7.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            @endif
                        </span>

                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span
                                        class="text-primary br-transparent relative -ml-px inline-flex cursor-default items-center px-4 py-2 text-sm leading-5 font-medium dark:text-gray-300"
                                    >
                                        {{ $element }}
                                    </span>
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                <span
                                                    class="text-primary dark:text-accent-add-dark br-transparent relative -ml-px inline-flex cursor-default items-center px-4 py-2 text-sm leading-5 font-medium"
                                                >
                                                    {{ $page }}
                                                </span>
                                            </span>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                                class="text-accent br-transparent hover:text-accent-add relative -ml-px inline-flex cursor-pointer items-center px-4 py-2 text-sm leading-5 font-medium transition duration-150 ease-in-out"
                                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                            >
                                                {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        @endforeach

                        <span>
                            {{-- Next Page Link --}}

                            @if ($paginator->hasMorePages())
                                <button
                                    type="button"
                                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                                    class="text-accent br-transparent hover:text-accent-add relative -ml-px inline-flex cursor-pointer items-center px-2 py-2 text-sm leading-5 font-medium transition duration-150 ease-in-out"
                                    aria-label="{{ __('pagination.next') }}"
                                >
                                    <svg class="h-7.5 w-7.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                    <span
                                        class="br-transparent relative -ml-px inline-flex cursor-default items-center px-2 py-2 text-sm leading-5 font-medium text-[#A7C1D2]"
                                        aria-hidden="true"
                                    >
                                        <svg class="h-7.5 w-7.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
