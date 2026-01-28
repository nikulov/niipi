<h2>{{ __('panel.submission') }}</h2>

<p>
    <strong>{{ __('panel.form') }}:</strong>
    {{ $submission->form?->name ?? '—' }}
</p>

<p>
    <strong>{{ __('panel.created_at') }}:</strong>
    {{ $submission->created_at?->format('d.m.Y H:i') ?? '—' }}
</p>

<hr />

<table cellpadding="6" cellspacing="0" border="0">
    @foreach (($submission->form?->fields ?? collect())->where('is_enabled', true)->sortBy('sort') as $field)
        @php
            $value = $submission->data[$field->name] ?? null;
        @endphp

        <tr>
            <td><strong>{{ $field->label }}</strong></td>
            <td>{{ is_string($value) && trim($value) !== '' ? $value : ($value === null ? '—' : (string) $value) }}</td>
        </tr>
    @endforeach
</table>

@if (($submission->files ?? collect())->count())
    <hr />
    <p><strong>Files:</strong></p>
    <ul>
        @foreach ($submission->files as $file)
            <li>{{ $file->original_name ?? $file->path }}</li>
        @endforeach
    </ul>
@endif
