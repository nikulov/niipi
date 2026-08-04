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

        // Тема — почтовый заголовок, не HTML: экранированные сущности
        // получатель увидел бы как есть («Иванов &amp; Партнёры»).
        return $this->replacePlaceholders($template, $context, escape: false);
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

    private function replacePlaceholders(string $template, array $context, bool $escape = true): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', function (array $m) use ($context, $escape) {
            $key = $m[1];

            $value = Arr::get($context, $key);

            if (is_array($value)) {
                return '';
            }

            if ($value === null) {
                return '';
            }

            // Важно: экранируем, чтобы юзер не смог инжектить HTML в письма.
            return $escape ? e((string) $value) : (string) $value;
        }, $template) ?? $template;
    }

    private function buildContext(FormSubmission $submission): array
    {
        $data = is_array($submission->data) ? $submission->data : [];

        $files = $submission->relationLoaded('files')
            ? $submission->files
            : $submission->files()->get();

        $field = $data;

        foreach ($files->groupBy('field_name') as $fieldName => $group) {
            if ((string) $fieldName === '') {
                continue;
            }

            $urls = $group
                ->map(fn ($f) => $f->url)
                ->filter()
                ->values();

            if ($urls->isEmpty()) {
                continue;
            }

            $field[$fieldName] = $urls->implode("\n");
        }

        $filesList = $files->map(function ($f) {
            $name = (string) ($f->original_name ?? 'file');
            $url = $f->url;

            if (! $url) {
                return '- '.$name;
            }

            $mdName = str_replace(['\\', '[', ']'], ['\\\\', '\\[', '\\]'], $name);

            return '- ['.$mdName.']('.$url.')';
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
            'field' => $field,    // {{ field.email }}, {{ field.cv }} (file → URL)
            'files' => $filesList, // {{ files }}
        ];
    }
}
