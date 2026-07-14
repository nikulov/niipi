<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Project;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $project = new Project();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [Project::class, $project]),
                $ability
            );
        }
    }

    public function test_editor_can_read_and_write_but_not_delete(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new ProjectPolicy();
        $project = new Project();

        $this->assertTrue($policy->viewAny($editor));
        $this->assertTrue($policy->view($editor, $project));
        $this->assertTrue($policy->create($editor));
        $this->assertTrue($policy->update($editor, $project));
        $this->assertFalse($policy->delete($editor, $project));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new ProjectPolicy();
        $project = new Project();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $project));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $project));
        $this->assertFalse($policy->delete($viewer, $project));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
