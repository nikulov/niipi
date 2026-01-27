<h2>{{ __('panel.submission_confirmation') }}</h2>

<p>{{ __('panel.submission_thanks') }}</p>

<p>
    <strong>{{ __('panel.form') }}:</strong>
    {{ $submission->form?->name ?? '—' }}
</p>

<p>
    <strong>{{ __('panel.created_at') }}:</strong>
    {{ $submission->created_at?->format('d.m.Y H:i') ?? '—' }}
</p>
