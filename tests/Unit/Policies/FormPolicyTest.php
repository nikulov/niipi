<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Form;
use App\Policies\FormPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FormPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $form = new Form();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [Form::class, $form]),
                $ability
            );
        }
    }

    public function test_editor_denied(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new FormPolicy();
        $form = new Form();

        $this->assertFalse($policy->viewAny($editor));
        $this->assertFalse($policy->view($editor, $form));
        $this->assertFalse($policy->create($editor));
        $this->assertFalse($policy->update($editor, $form));
        $this->assertFalse($policy->delete($editor, $form));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new FormPolicy();
        $form = new Form();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $form));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $form));
        $this->assertFalse($policy->delete($viewer, $form));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
