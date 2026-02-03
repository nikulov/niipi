<?php

namespace Tests\Unit\Services\Forms;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormSubmissionFile;
use App\Services\Forms\SubmissionFilesStorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionFilesStorerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stores_single_and_multiple_files(): void
    {
        Storage::fake('public');

        $form = Form::create([
            'name' => 'files',
            'title' => 'Files',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'avatar',
            'label' => 'Avatar',
            'is_enabled' => true,
            'extra' => [
                'disk' => 'public',
                'dir' => 'forms/../evil/1',
            ],
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'docs',
            'label' => 'Docs',
            'is_enabled' => true,
            'extra' => [
                'disk' => 'public',
                'dir' => 'forms/docs',
                'multiple' => true,
            ],
        ]);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'status' => 'new',
            'data' => [],
        ]);

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $doc1 = UploadedFile::fake()->create('doc1.pdf', 10, 'application/pdf');
        $doc2 = UploadedFile::fake()->create('doc2.pdf', 12, 'application/pdf');

        $storer = new SubmissionFilesStorer();
        $storer->store($form, $submission, [
            'avatar' => $avatar,
            'docs' => [$doc1, $doc2],
        ]);

        $files = FormSubmissionFile::query()
            ->where('form_submission_id', $submission->id)
            ->get();

        $this->assertCount(3, $files);

        foreach ($files as $file) {
            $this->assertSame('public', $file->disk);
            $this->assertStringNotContainsString('..', $file->path);
            $this->assertStringNotContainsString('\\', $file->path);
            Storage::disk('public')->assertExists($file->path);
        }
    }
}
