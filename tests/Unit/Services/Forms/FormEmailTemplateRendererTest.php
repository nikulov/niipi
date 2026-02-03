<?php

namespace Tests\Unit\Services\Forms;

use App\Enums\FormSubmissionStatus;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use App\Services\Forms\FormEmailTemplateRenderer;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FormEmailTemplateRendererTest extends TestCase
{
    public function test_replaces_placeholders_and_escapes_values(): void
    {
        $submission = new FormSubmission([
            'form_id' => 1,
            'status' => FormSubmissionStatus::Processing,
            'data' => [
                'email' => 'user@example.com',
                'name' => '<b>Bob</b>',
            ],
        ]);
        $submission->id = 10;
        $submission->created_at = Carbon::parse('2026-02-03 10:00:00');
        $submission->setRelation('form', new Form(['name' => 'Contact']));

        $renderer = new FormEmailTemplateRenderer();

        $subject = $renderer->renderSubject(
            $submission,
            'Hello {{ field.name }} {{ field.email }} {{ form.name }} {{ submission.id }} {{ submission.status }} {{ submission.created_at }}'
        );

        $this->assertStringContainsString('Hello', $subject);
        $this->assertStringContainsString('&lt;b&gt;Bob&lt;/b&gt;', $subject);
        $this->assertStringContainsString('user@example.com', $subject);
        $this->assertStringContainsString('Contact', $subject);
        $this->assertStringContainsString('10', $subject);
        $this->assertStringContainsString('processing', $subject);
        $this->assertStringContainsString('03.02.2026 10:00', $subject);
    }

    public function test_renders_files_list_in_body(): void
    {
        $submission = new FormSubmission([
            'form_id' => 1,
            'status' => FormSubmissionStatus::New,
            'data' => [],
        ]);
        $submission->id = 11;

        $file = new FormSubmissionFile([
            'field_name' => 'file',
            'path' => 'forms/1/11/file.pdf',
            'original_name' => 'file.pdf',
        ]);
        $file->setAttribute('url', 'http://example.com/file.pdf');

        $submission->setRelation('files', collect([$file]));

        $renderer = new FormEmailTemplateRenderer();

        $text = $renderer->renderBodyText($submission, "Files:\n{{ files }}");
        $this->assertStringContainsString('file.pdf', $text);
        $this->assertStringContainsString('http://example.com/file.pdf', $text);

        $html = $renderer->renderBodyHtml($submission, "**Files**\n\n{{ files }}");
        $this->assertStringContainsString('<strong>Files</strong>', $html);
        $this->assertStringContainsString('file.pdf', $html);
    }
}
