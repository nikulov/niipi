<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\FormSubmission;
use App\Policies\FormSubmissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FormSubmissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $submission = new FormSubmission();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [FormSubmission::class, $submission]),
                $ability
            );
        }
    }

    public function test_editor_denied(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new FormSubmissionPolicy();
        $submission = new FormSubmission();

        $this->assertFalse($policy->viewAny($editor));
        $this->assertFalse($policy->view($editor, $submission));
        $this->assertFalse($policy->create($editor));
        $this->assertFalse($policy->update($editor, $submission));
        $this->assertFalse($policy->delete($editor, $submission));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new FormSubmissionPolicy();
        $submission = new FormSubmission();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $submission));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $submission));
        $this->assertFalse($policy->delete($viewer, $submission));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
