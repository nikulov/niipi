<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Page;
use App\Policies\PagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PagePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $page = new Page();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [Page::class, $page]),
                $ability
            );
        }
    }

    public function test_editor_denied(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new PagePolicy();
        $page = new Page();

        $this->assertFalse($policy->viewAny($editor));
        $this->assertFalse($policy->view($editor, $page));
        $this->assertFalse($policy->create($editor));
        $this->assertFalse($policy->update($editor, $page));
        $this->assertFalse($policy->delete($editor, $page));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new PagePolicy();
        $page = new Page();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $page));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $page));
        $this->assertFalse($policy->delete($viewer, $page));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
