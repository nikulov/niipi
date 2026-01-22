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
                    <label class="block text-sm font-medium text-gray-700">
                        {{ $field['label'] }}
                        @if ($field['required'])
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @if (in_array($field['type'], ['text', 'email', 'phone'], true))
                        <input
                            type="{{ $field['inputType'] }}"
                            wire:model.defer="{{ $field['wireModel'] }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        />
                    @endif

                    @if ($field['type'] === 'textarea')
                        <textarea
                            wire:model.defer="{{ $field['wireModel'] }}"
                            rows="4"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        ></textarea>
                    @endif

                    @if ($field['type'] === 'select')
                        <select
                            wire:model.defer="{{ $field['wireModel'] }}"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
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
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
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
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endforeach

            <div class="pt-4">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                >
                    {{ $viewData['submitLabel'] }}
                </button>
            </div>
        </form>
    @endif
</div>
