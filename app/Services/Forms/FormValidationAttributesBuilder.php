<?php

namespace App\Services\Forms;

use App\Models\Form;

final class FormValidationAttributesBuilder
{
    public function build(Form $form): array
    {
        $attributes = [];
        
        foreach ($form->fields as $field) {
            if (! $field->is_enabled) {
                continue;
            }
            
            $isFile = $field->type === 'file';
            $key = $isFile ? "uploads.{$field->name}" : "data.{$field->name}";
            
            $attributes[$key] = (string) $field->label;
            
            // multiple file item errors: uploads.name.*
            $cfg = is_array($field->extra) ? $field->extra : [];
            if ($isFile && (bool) ($cfg['multiple'] ?? false)) {
                $attributes["uploads.{$field->name}.*"] = (string) $field->label;
            }
        }
        
        return $attributes;
    }
}