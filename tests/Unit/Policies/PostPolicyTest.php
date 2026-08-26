<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $post = new Post();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [Post::class, $post]),
                $ability
            );
        }
    }

    public function test_editor_can_read_and_write_but_not_delete(): void
    {
        $editor = $this->userOfRole(UserRole::Editor);
        $policy = new PostPolicy();
        $post = new Post();

        $this->assertTrue($policy->viewAny($editor));
        $this->assertTrue($policy->view($editor, $post));
        $this->assertTrue($policy->create($editor));
        $this->assertTrue($policy->update($editor, $post));
        $this->assertFalse($policy->delete($editor, $post));
        $this->assertFalse($policy->deleteAny($editor));
    }

    public function test_viewer_can_read_only(): void
    {
        $viewer = $this->userOfRole(UserRole::Viewer);
        $policy = new PostPolicy();
        $post = new Post();

        $this->assertTrue($policy->viewAny($viewer));
        $this->assertTrue($policy->view($viewer, $post));
        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $post));
        $this->assertFalse($policy->delete($viewer, $post));
        $this->assertFalse($policy->deleteAny($viewer));
    }
}
