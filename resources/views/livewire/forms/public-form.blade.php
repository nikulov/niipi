<section
    class="px-inner-section-x my-inner-section-y relative mx-auto w-full max-w-1242"
    data-form-key="{{ $componentKey }}"
    x-data="{ openSuccess: false }"
    x-on:form-submitted.window="
        $event.detail &&
            $event.detail.componentKey === '{{ $componentKey }}' &&
            (openSuccess = true)
    "
>
    <div class="mx-auto max-w-225">
        @if (! empty($viewData['title']) ?? null)
            <h2 class="text-primary dark:text-accent-add-dark mb-6">{{ $viewData['title'] }}</h2>
        @endif

        <form wire:submit.prevent="submit" class="w-full space-y-6">
            <div class="absolute top-auto -left-2500 h-px w-px overflow-hidden" aria-hidden="true">
                <label for="website" aria-hidden="true">{{ __('panel.website') }}</label>
                <input id="website" type="text" wire:model="website" autocomplete="off" tabindex="-1" />
            </div>

            @foreach ($viewData['fields'] as $field)
                <div class="relative w-full space-y-2">
                    <x-dynamic-component :component="$field['component']" :field="$field" />
                </div>
            @endforeach

            <div class="flex justify-center pt-4">
                <x-buttons.btn-add type="submit" label="{{$viewData['submitLabel']}}" class="mx-auto" />
            </div>
        </form>
    </div>

    <template x-teleport="body">
        <div
            class="fixed inset-0 z-60 w-full bg-black/50"
            x-cloak
            x-show="openSuccess"
            x-trap.noscroll.nofocus="openSuccess"
            @keydown.escape.window="openSuccess = false"
        >
            <div class="absolute h-full max-h-full w-full overflow-y-scroll overscroll-contain scheme-light dark:scheme-dark">
                <div class="bg-background-light dark:bg-background-dark relative mx-auto mt-10 w-full max-w-228">
                    <x-layout.main-section-border>
                        <div class="px-inner-section-x to-top-dark relative flow-root">
                            <div @click.prevent="openSuccess = false" class="cursor-pointer">
                                <x-icon.icon-close-cross
                                    class="fill-accent-add dark:fill-accent-add-dark top-inner-section-y absolute right-10 h-6 w-6"
                                />
                            </div>

                            <div
                                class="text-text dark:text-white-dark rich-editor py-inner-section-y flex flex-col items-center justify-center"
                            >
                                {!! $viewData['successMessage'] !!}
                            </div>
                        </div>
                    </x-layout.main-section-border>
                </div>
            </div>
        </div>
    </template>
</section>
