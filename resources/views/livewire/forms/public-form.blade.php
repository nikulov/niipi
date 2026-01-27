<div class="mx-auto max-w-xl">
    @if ($submitted)
        <div class="rounded-lg border border-green-200 bg-green-50 p-6 text-green-800">
            <strong class="block text-lg font-medium">
                {{ $viewData['successMessage'] }}
            </strong>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            @foreach ($viewData['fields'] as $field)
                <div class="space-y-2">
                    <x-form.label required="{{$field['required']}}">
                        {{ $field['label'] }}
                    </x-form.label>

                    @if (in_array($field['type'], ['text', 'email', 'phone'], true))
                        <input
                            type="{{ $field['inputType'] }}"
                            wire:model.defer="{{ $field['wireModel'] }}"
                            class="border-b-primary focus:border-accent w-full border-b px-3 py-2 text-sm focus:outline-none"
                        />
                    @endif

                    @if ($field['type'] === 'textarea')
                        <textarea
                            wire:model.defer="{{ $field['wireModel'] }}"
                            rows="4"
                            class="border-b-primary focus:border-accent w-full border-b px-3 py-2 text-sm focus:outline-none"
                        ></textarea>
                    @endif

                    @if ($field['type'] === 'select')
                        <select
                            wire:model.defer="{{ $field['wireModel'] }}"
                            class="border-b-primary focus:border-accent w-full border-b px-3 py-2 text-sm focus:outline-none"
                        >
                            <option value="">—</option>
                            @foreach ($field['options'] as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    @endif

                    @if ($field['type'] === 'checkbox')
                        <label class="inline-flex items-center gap-2">
                            <input
                                type="checkbox"
                                wire:model.defer="{{ $field['wireModel'] }}"
                                value="1"
                                class="text-primary border-primary h-4 w-4 rounded"
                            />
                            <span class="text-sm text-gray-600">
                                {{ $field['label'] }}
                            </span>
                        </label>
                    @endif

                    @if ($field['type'] === 'file')
                        <input
                            type="file"
                            wire:model="{{ $field['wireModel'] }}"
                            class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                        />
                    @endif

                    @error($field['errorKey'])
                        <p class="text-sm text-red-800">
                            {{ $message }}
                        </p>
                    @enderror
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
