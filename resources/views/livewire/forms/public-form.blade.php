@php($isModal = (bool) ($viewData["isModal"] ?? false))

<section
    class="px-inner-section-x my-inner-section-y relative mx-auto w-full max-w-1242"
    @if ($isModal)
        x-data="{
            open: false,
            hash: '#{{ $form->slug }}',
            sync() {
                this.open = window.location.hash === this.hash
            },
            close() {
                window.location.hash = ''
            },
        }"
        x-init="
            sync()
            window.addEventListener('hashchange', () => sync())
        "
    @endif
>
    <div
        @class([$isModal ? "fixed inset-0 z-50 w-full bg-black/50" : "contents"])
        @if ($isModal)
            x-cloak
            x-show="open"
            x-trap.noscroll="open"
            @keydown.escape.window="close()"
        @endif
    >
        <div @class([$isModal ? "absolute inset-0 flex w-full items-end p-4 sm:items-center sm:justify-center" : ""])>
            <div @class([$isModal ? "bg-background-light dark:bg-background-dark relative w-full max-w-225" : ""])>
                <x-layout.main-section-border>
                    @if ($isModal)
                        <div @click="close" class="cursor-pointer">
                            <x-icon.icon-close-cross class="fill-accent-add dark:fill-accent-add-dark absolute top-10 right-10 h-6 w-6" />
                        </div>
                    @endif

                    <div class="py-inner-section-y px-20">
                        @if ($submitted)
                            <div class="text-text dark:text-white-dark flex flex-col items-center justify-center p-6">
                                <strong class="text-big block text-center font-medium">
                                    {{ $viewData["successMessage"] }}
                                </strong>
                                <x-buttons.btn-add @click="close" type="" label="Закрыть" class="mx-auto mt-6" />
                            </div>
                        @else
                            @if (! empty($viewData["title"]) ?? null)
                                <h2 class="text-primary dark:text-accent-add-dark mb-6">{{ $viewData["title"] }}</h2>
                            @endif

                            <form wire:submit.prevent="submit" class="w-full space-y-6">
                                <div class="absolute top-auto -left-2500 h-px w-px overflow-hidden" aria-hidden="true">
                                    <label for="website">Website</label>
                                    <input id="website" type="text" wire:model="website" autocomplete="off" tabindex="-1" />
                                </div>

                                @foreach ($viewData["fields"] as $field)
                                    <div class="relative w-full space-y-2">
                                        <x-dynamic-component :component="$field['component']" :field="$field" />
                                    </div>
                                @endforeach

                                <div class="flex justify-center pt-4">
                                    <x-buttons.btn-add type="submit" label="{{$viewData['submitLabel']}}" class="mx-auto" />
                                </div>
                            </form>
                        @endif
                    </div>
                </x-layout.main-section-border>
            </div>
        </div>
    </div>
</section>
