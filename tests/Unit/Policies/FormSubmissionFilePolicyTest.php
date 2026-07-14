<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\FormSubmissionFile;
use App\Policies\FormSubmissionFilePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FormSubmissionFilePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $file = new FormSubmissionFile();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [FormSubmissionFile::class, $file]),
                $ability
            );
        }
    }

    public function test_editor_denied(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new FormSubmissionFilePolicy();
        $file = new FormSubmissionFile();

        $this->assertFalse($policy->viewAny($editor));
        $this->assertFalse($policy->view($editor, $file));
        $this->assertFalse($policy->create($editor));
        $this->assertFalse($policy->update($editor, $file));
        $this->assertFalse($policy->delete($editor, $file));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new FormSubmissionFilePolicy();
        $file = new FormSubmissionFile();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $file));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $file));
        $this->assertFalse($policy->delete($viewer, $file));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
