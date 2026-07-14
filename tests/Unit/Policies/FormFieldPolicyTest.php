<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\FormField;
use App\Policies\FormFieldPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FormFieldPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $field = new FormField();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [FormField::class, $field]),
                $ability
            );
        }
    }

    public function test_editor_denied(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new FormFieldPolicy();
        $field = new FormField();

        $this->assertFalse($policy->viewAny($editor));
        $this->assertFalse($policy->view($editor, $field));
        $this->assertFalse($policy->create($editor));
        $this->assertFalse($policy->update($editor, $field));
        $this->assertFalse($policy->delete($editor, $field));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new FormFieldPolicy();
        $field = new FormField();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $field));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $field));
        $this->assertFalse($policy->delete($viewer, $field));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
