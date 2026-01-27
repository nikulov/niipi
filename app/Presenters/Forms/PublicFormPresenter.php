<?php

namespace App\Presenters\Forms;

use App\Models\Form;

final class PublicFormPresenter
{
    public function present(Form $form): array
    {
        $settings = is_array($form->settings) ? $form->settings : [];
        
        return [
            'id' => (int) $form->id,
            'slug' => (string) $form->slug,
            'title' => (string) $form->name,
            'layout' => (string) ($settings['layout'] ?? 'inline'),
            'submitLabel' => (string) ($settings['submit_label'] ?? 'Submit'),
            'successMessage' => (string) ($settings['success_message'] ?? 'Thank you! Your message has been sent.'),
            'fields' => $this->presentFields($form),
        ];
    }
    
    private function presentFields(Form $form): array
    {
        $out = [];
        
        foreach ($form->fields as $field) {
            if (! $field->is_enabled) {
                continue;
            }
            
            $isFile = $field->type === 'file';
            
            $out[] = [
                'key' => (string) $field->id,
                'type' => (string) $field->type,
                'name' => (string) $field->name,
                'label' => (string) $field->label,
                'required' => (bool) $field->required,
                'wireModel' => $isFile ? "uploads.{$field->name}" : "data.{$field->name}",
                'errorKey' => $isFile ? "uploads.{$field->name}" : "data.{$field->name}",
                'inputType' => $this->mapInputType((string) $field->type),
                'options' => $this->normalizeOptions($field->options),
            ];
        }
        
        return $out;
    }
    
    private function mapInputType(string $type): ?string
    {
        return match ($type) {
            'text' => 'text',
            'email' => 'email',
            default => null,
        };
    }
    
    private function normalizeOptions($options): array
    {
        if (! is_array($options)) {
            return [];
        }
        
        $out = [];
        
        foreach ($options as $row) {
            if (! is_array($row)) {
                continue;
            }
            
            $value = (string) ($row['value'] ?? '');
            if ($value === '') {
                continue;
            }
            
            $out[] = [
                'value' => $value,
                'label' => (string) ($row['label'] ?? $value),
            ];
        }
        
        return $out;
    }
}