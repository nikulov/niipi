<section class="px-inner-section-x my-inner-section-y relative mx-auto w-full max-w-1242">
    <div class="mx-auto max-w-225">
        @if ($submitted)
            <div class="text-text dark:text-white-dark flex flex-col items-center justify-center p-6">
                <strong class="text-big block text-center font-medium">
                    {{ $viewData['successMessage'] }}
                </strong>
            </div>
        @else
            @if (! empty($viewData['title']) ?? null)
                <h2 class="text-primary dark:text-accent-add-dark mb-6">{{ $viewData['title'] }}</h2>
            @endif

            <form wire:submit.prevent="submit" class="w-full space-y-6">
                <div class="absolute top-auto -left-2500 h-px w-px overflow-hidden" aria-hidden="true">
                    <label for="website">Website</label>
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
        @endif
    </div>
</section>
