<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Policies\CategoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $category = new Category();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [Category::class, $category]),
                $ability
            );
        }
    }

    public function test_editor_can_read_and_write_but_not_delete(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new CategoryPolicy();
        $category = new Category();

        $this->assertTrue($policy->viewAny($editor));
        $this->assertTrue($policy->view($editor, $category));
        $this->assertTrue($policy->create($editor));
        $this->assertTrue($policy->update($editor, $category));
        $this->assertFalse($policy->delete($editor, $category));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new CategoryPolicy();
        $category = new Category();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $category));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $category));
        $this->assertFalse($policy->delete($viewer, $category));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
