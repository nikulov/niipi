<?php

namespace Tests\Unit\Services\Forms;

use App\Enums\FormSubmissionStatus;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use App\Services\Forms\FormEmailTemplateRenderer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormEmailTemplateRendererTest extends TestCase
{
    public function test_replaces_placeholders_in_subject_without_escaping(): void
    {
        $submission = new FormSubmission([
            'form_id' => 1,
            'status' => FormSubmissionStatus::Processing,
            'data' => [
                'email' => 'user@example.com',
                'name' => '<b>Bob</b>',
                'company' => 'Иванов & Партнёры',
            ],
        ]);
        $submission->id = 10;
        $submission->created_at = Carbon::parse('2026-02-03 10:00:00');
        $submission->setRelation('form', new Form(['name' => 'Contact']));

        $renderer = new FormEmailTemplateRenderer;

        $subject = $renderer->renderSubject(
            $submission,
            'Hello {{ field.name }} {{ field.company }} {{ field.email }} {{ form.name }} {{ submission.id }} {{ submission.status }} {{ submission.created_at }}'
        );

        $this->assertStringContainsString('Hello', $subject);
        $this->assertStringContainsString('user@example.com', $subject);
        $this->assertStringContainsString('Contact', $subject);
        $this->assertStringContainsString('10', $subject);
        $this->assertStringContainsString('processing', $subject);
        $this->assertStringContainsString('03.02.2026 10:00', $subject);

        // тема уходит в почтовый заголовок, а не в HTML: сущности получатель
        // увидел бы буквально
        $this->assertStringContainsString('Иванов & Партнёры', $subject);
        $this->assertStringContainsString('<b>Bob</b>', $subject);
        $this->assertStringNotContainsString('&amp;', $subject);
        $this->assertStringNotContainsString('&lt;', $subject);
    }

    public function test_html_body_still_escapes_values(): void
    {
        $submission = new FormSubmission([
            'form_id' => 1,
            'status' => FormSubmissionStatus::Processing,
            'data' => [
                'name' => '<b>Bob</b>',
                'company' => 'Иванов & Партнёры',
            ],
        ]);
        $submission->id = 10;
        $submission->setRelation('form', new Form(['name' => 'Contact']));

        $html = (new FormEmailTemplateRenderer)
            ->renderBodyHtml($submission, '{{ field.name }} / {{ field.company }}');

        // защита от инъекции HTML в письмо — здесь экранирование обязано остаться
        $this->assertStringContainsString('&lt;b&gt;Bob&lt;/b&gt;', $html);
        $this->assertStringContainsString('Иванов &amp; Партнёры', $html);
        $this->assertStringNotContainsString('<b>Bob</b>', $html);
    }

    public function test_renders_files_list_in_body(): void
    {
        Storage::fake('public');

        $submission = new FormSubmission([
            'form_id' => 1,
            'status' => FormSubmissionStatus::New,
            'data' => [],
        ]);
        $submission->id = 11;

        $file = new FormSubmissionFile([
            'field_name' => 'file',
            'disk' => 'public',
            'path' => 'forms/1/11/file.pdf',
            'original_name' => 'file.pdf',
        ]);

        $submission->setRelation('files', collect([$file]));

        $renderer = new FormEmailTemplateRenderer;
        $expectedUrl = Storage::disk('public')->url('forms/1/11/file.pdf');

        $text = $renderer->renderBodyText($submission, "Files:\n{{ files }}");
        $this->assertStringContainsString('file.pdf', $text);
        $this->assertStringContainsString($expectedUrl, $text);

        $html = $renderer->renderBodyHtml($submission, "**Files**\n\n{{ files }}");
        $this->assertStringContainsString('<strong>Files</strong>', $html);
        $this->assertStringContainsString('file.pdf', $html);
    }
}
