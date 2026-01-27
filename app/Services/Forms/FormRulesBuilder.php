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
                $cfg = is_array($field->extra) ? $field->extra : [];
                $fileRules = $this->buildFileRules($field->name, $cfg, (bool) $field->required, $extra);
                
                $rules = array_merge($rules, $fileRules);
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
    
    private function buildFileRules(string $name, array $cfg, bool $required, array $extraRules): array
    {
        $multiple = (bool) ($cfg['multiple'] ?? false);
        $maxFiles = $multiple ? max(1, (int) ($cfg['max_files'] ?? 1)) : 1;
        $maxSizeKb = max(1, (int) ($cfg['max_size_kb'] ?? 5120));
        
        $mimes = [];
        $raw = $cfg['accept_mimes'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $m) {
                if (is_string($m) && $m !== '') {
                    $mimes[] = $m;
                }
            }
        }
        
        $mimetypesRule = count($mimes) ? ('mimetypes:' . implode(',', $mimes)) : null;
        
        if (! $multiple) {
            $rules = [];
            $rules[] = $required ? 'required' : 'nullable';
            $rules[] = 'file';
            if ($mimetypesRule) {
                $rules[] = $mimetypesRule;
            }
            $rules[] = 'max:' . $maxSizeKb;
            
            // allow extra custom rules from admin (rare but keep)
            $rules = array_merge($rules, $extraRules);
            
            return [
                "uploads.$name" => $rules,
            ];
        }
        
        $container = [];
        $container[] = $required ? 'required' : 'nullable';
        $container[] = 'array';
        $container[] = 'max:' . $maxFiles;
        
        $each = [];
        $each[] = 'file';
        if ($mimetypesRule) {
            $each[] = $mimetypesRule;
        }
        $each[] = 'max:' . $maxSizeKb;
        
        // extra rules apply to each file
        $each = array_merge($each, $extraRules);
        
        return [
            "uploads.$name" => $container,
            "uploads.$name.*" => $each,
        ];
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