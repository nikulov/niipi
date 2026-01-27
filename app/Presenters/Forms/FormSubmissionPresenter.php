<?php

namespace App\Presenters\Forms;

use App\Models\FormSubmission;

final class FormSubmissionPresenter
{
    public function rows(FormSubmission $submission): array
    {
        $fields = $submission->form?->fields ?? collect();
        $data = is_array($submission->data) ? $submission->data : [];
        
        return $fields
            ->where('is_enabled', true)
            ->sortBy('sort')
            ->map(function ($field) use ($data) {
                $label = (string)$field->label;
                $name = (string)$field->name;
                $type = (string)$field->type;
                
                $raw = $data[$name] ?? null;
                
                return [
                    'label' => $label,
                    'value' => $this->formatValue($type, $raw),
                ];
            })
            ->values()
            ->all();
    }
    
    private function formatValue(string $type, $raw): string
    {
        if ($raw === null) {
            return '—';
        }
        
        if (is_string($raw) && trim($raw) === '') {
            return '—';
        }
        
        if ($type === 'checkbox') {
            return (bool)$raw ? __('panel.yes') : __('panel.no');
        }
        
        if (is_array($raw)) {
            return json_encode($raw, JSON_UNESCAPED_UNICODE) ?: '—';
        }
        
        return (string)$raw;
    }
}