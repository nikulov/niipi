<div class="space-y-4">
    @if ($error)
        <div class="bg-danger-50 text-danger-700 dark:bg-danger-950 dark:text-danger-200 rounded-lg p-4 text-sm">
            {{ $error }}
        </div>
    @else
        <div class="text-sm">
            <div class="font-semibold">{{ __('panel.email_subject') }}:</div>
            <div class="mt-1 wrap-break-word">{{ $subject }}</div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="prose dark:prose-invert max-w-none p-4">
                {!! $html !!}
            </div>
        </div>
    @endif
</div>
