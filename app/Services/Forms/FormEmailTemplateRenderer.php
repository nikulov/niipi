<?php

namespace App\Services\Forms;

use App\Models\FormSubmission;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class FormEmailTemplateRenderer
{
    public function renderSubject(FormSubmission $submission, string $template): string
    {
        $context = $this->buildContext($submission);
        
        return $this->replacePlaceholders($template, $context);
    }
    
    public function renderBodyHtml(FormSubmission $submission, string $templateMd): string
    {
        $context = $this->buildContext($submission);
        
        $md = $this->replacePlaceholders($templateMd, $context);
        
        return (string) Str::markdown($md);
    }
    
    public function renderBodyText(FormSubmission $submission, string $templateMd): string
    {
        $context = $this->buildContext($submission);
        
        return $this->replacePlaceholders($templateMd, $context);
    }
    
    private function replacePlaceholders(string $template, array $context): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', function (array $m) use ($context) {
            $key = $m[1];
            
            $value = Arr::get($context, $key);
            
            if (is_array($value)) {
                return '';
            }
            
            if ($value === null) {
                return '';
            }
            
            // Важно: экранируем, чтобы юзер не смог инжектить HTML в письма.
            return e((string) $value);
        }, $template) ?? $template;
    }
    
    private function buildContext(FormSubmission $submission): array
    {
        $data = is_array($submission->data) ? $submission->data : [];
        
        $files = $submission->relationLoaded('files')
            ? $submission->files
            : collect();
        
        $filesListMd = $files->map(function ($f) {
            $name = $f->original_name ?? $f->name ?? 'file';
            $url = $f->url ?? $f->public_url ?? null;
            
            return $url
                ? "- {$name}: {$url}"
                : "- {$name}";
        })->implode("\n");
        
        return [
            'form' => [
                'id' => $submission->form_id,
                'name' => $submission->form?->name,
            ],
            'submission' => [
                'id' => $submission->id,
                'created_at' => optional($submission->created_at)?->format('d.m.Y H:i'),
                'status' => $submission->status?->value ?? (string) $submission->status,
            ],
            'field' => $data,      // {{ field.email }}, {{ field.name }} ...
            'files' => $filesListMd, // {{ files }}
        ];
    }
}