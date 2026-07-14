<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $other = new User();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [User::class, $other]),
                $ability
            );
        }
    }

    public function test_editor_denied(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new UserPolicy();
        $other = new User();

        $this->assertFalse($policy->viewAny($editor));
        $this->assertFalse($policy->view($editor, $other));
        $this->assertFalse($policy->create($editor));
        $this->assertFalse($policy->update($editor, $other));
        $this->assertFalse($policy->delete($editor, $other));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new UserPolicy();
        $other = new User();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $other));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $other));
        $this->assertFalse($policy->delete($viewer, $other));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
