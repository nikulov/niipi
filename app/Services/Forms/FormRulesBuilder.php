<?php

namespace App\Services\Forms;

use App\Models\Form;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

final class FormRulesBuilder
{
    public function build(Form $form): array
    {
        $rules = [];
        
        foreach ($form->fields as $field) {
            if (! $field->is_enabled) {
                continue;
            }
            
            $keyData = "data.{$field->name}";
            $keyUpload = "uploads.{$field->name}";
            
            $base = $field->required ? ['required'] : ['nullable'];
            
            $extra = is_array($field->rules) ? array_values($field->rules) : [];
            
            if ($field->type === 'file') {
                $rules[$keyUpload] = array_merge($base, $this->fileRules($field->extra, $extra));
                continue;
            }
            
            if ($field->type === 'email') {
                $rules[$keyData] = array_merge($base, ['email'], $extra);
                continue;
            }
            
            if (in_array($field->type, ['select', 'radio'], true)) {
                $values = $this->optionValues($field->options);
                $inRule = count($values) > 0 ? [Rule::in($values)] : [];
                $rules[$keyData] = array_merge($base, $inRule, $extra);
                continue;
            }
            
            if ($field->type === 'checkbox') {
                $rules[$keyData] = array_merge($base, ['boolean'], $extra);
                continue;
            }
            
            $rules[$keyData] = array_merge($base, $extra);
        }
        
        return $rules;
    }
    
    private function fileRules($extra, array $extraRules): array
    {
        $rules = ['file'];
        
        $rules = array_merge($rules, $extraRules);
        
        $mimes = Arr::get($extra, 'mimes');
        if (is_array($mimes) && count($mimes) > 0) {
            $rules[] = 'mimes:' . implode(',', $mimes);
        }
        
        $maxKb = Arr::get($extra, 'max_kb');
        if (is_numeric($maxKb)) {
            $rules[] = 'max:' . (int) $maxKb;
        }
        
        return $rules;
    }
    
    private function optionValues($options): array
    {
        if (! is_array($options)) {
            return [];
        }
        
        return collect($options)
            ->map(fn ($row) => is_array($row) ? ($row['value'] ?? null) : null)
            ->filter(fn ($v) => is_string($v) && $v !== '')
            ->values()
            ->all();
    }
}