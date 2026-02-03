<?php

namespace Tests\Unit\Services\Forms;

use App\Enums\FormSubmissionStatus;
use App\Models\Form;
use App\Services\Forms\SubmissionCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_submission_with_expected_fields(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        $creator = new SubmissionCreator();
        $submission = $creator->create($form, ['name' => 'Alice'], '127.0.0.1', 'UA');

        $this->assertSame($form->id, $submission->form_id);
        $this->assertSame(['name' => 'Alice'], $submission->data);
        $this->assertSame('127.0.0.1', $submission->ip);
        $this->assertSame('UA', $submission->user_agent);
        $this->assertTrue($submission->status === FormSubmissionStatus::New);
    }
}
