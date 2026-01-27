<div class="mx-auto max-w-xl">
    @if ($submitted)
        <div class="rounded-lg border border-green-200 bg-green-50 p-6 text-green-800">
            <strong class="block text-lg font-medium">
                {{ $viewData['successMessage'] }}
            </strong>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            <div style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden">
                <label for="website">Website</label>
                <input id="website" type="text" wire:model.defer="website" autocomplete="off" tabindex="-1" />
            </div>

            @foreach ($viewData['fields'] as $field)
                <div class="space-y-2">
                    <x-dynamic-component :component="$field['component']" :field="$field" />
                </div>
            @endforeach

            <div class="pt-4">
                <button type="submit" class="btn-primary group btn btn-primary-bg cursor-pointer no-underline focus:outline-none">
                    <div
                        class="btn-primary-bg absolute top-0.75 -left-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
                    ></div>

                    <span class="btn-primary-text btn-text">{{ $viewData['submitLabel'] }}</span>

                    <div
                        class="btn-primary-bg absolute -right-0.75 bottom-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
                    ></div>
                </button>
            </div>
        </form>
    @endif
</div>
