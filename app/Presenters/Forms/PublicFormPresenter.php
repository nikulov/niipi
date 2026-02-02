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
            'title' => (string) $form->title,
            'isModal' => (bool) $form->is_modal,
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
            
            $extra = is_array($field->extra) ? $field->extra : [];
            
            $out[] = [
                'key' => (string) $field->id,
                'type' => (string) $field->type,
                'name' => (string) $field->name,
                'label' => (string) $field->label,
                'placeholder' => (string) $field->placeholder,
                'required' => (bool) $field->required,
                'wireModel' => $isFile ? "uploads.{$field->name}" : "data.{$field->name}",
                'errorKey' => $isFile ? "uploads.{$field->name}" : "data.{$field->name}",
                'inputType' => $this->mapInputType((string) $field->type),
                'options' => $this->normalizeOptions($field->options),
                'component' => $this->mapComponent((string) $field->type),
                'default' => $this->extractDefault($field->options),
                'file' => $isFile ? $this->presentFileConfig($extra) : null,
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
    
    private function mapComponent(string $type): string
    {
        return match ($type) {
            'text', 'email', 'phone' => 'form.fields.input',
            'textarea' => 'form.fields.textarea',
            'select' => 'form.fields.select',
            'radio' => 'form.fields.radio',
            'checkbox' => 'form.fields.checkbox',
            'file' => 'form.fields.file',
            default => 'form.fields.input',
        };
    }
    
    private function extractDefault($options): ?string
    {
        if (!is_array($options)) {
            return null;
        }
        
        foreach ($options as $row) {
            if (
                is_array($row)
                && !empty($row['default'])
                && !empty($row['value'])
            ) {
                return (string) $row['value'];
            }
        }
        
        return null;
    }
    
    private function presentFileConfig(array $extra): array
    {
        $multiple = (bool) ($extra['multiple'] ?? false);
        
        $accept = [];
        $rawMimes = $extra['accept_mimes'] ?? [];
        if (is_array($rawMimes)) {
            foreach ($rawMimes as $m) {
                if (is_string($m) && $m !== '') {
                    $accept[] = $m;
                }
            }
        }
        
        return [
            'multiple' => $multiple,
            'maxFiles' => $multiple ? max(1, (int) ($extra['max_files'] ?? 1)) : 1,
            'maxSizeKb' => max(1, (int) ($extra['max_size_kb'] ?? 5120)),
            'acceptMimes' => $accept,
            'acceptAttr' => count($accept) ? implode(',', $accept) : null,
        ];
    }
}