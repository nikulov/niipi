<?php

namespace App\Services\Forms;

use App\Models\Form;

final class SubmissionDataNormalizer
{
    public function normalize(Form $form, array $validated): array
    {
        $data = $validated['data'] ?? [];
        
        $out = [];
        
        foreach ($form->fields as $field) {
            if (! $field->is_enabled) {
                continue;
            }
            
            if ($field->type === 'file') {
                continue;
            }
            
            $name = $field->name;
            $value = $data[$name] ?? null;
            
            if ($field->type === 'checkbox') {
                $out[$name] = (bool) $value;
                continue;
            }
            
            if ($value === null) {
                continue;
            }
            
            if (is_string($value) && trim($value) === '') {
                continue;
            }
            
            $out[$name] = $value;
        }
        
        return $out;
    }
}