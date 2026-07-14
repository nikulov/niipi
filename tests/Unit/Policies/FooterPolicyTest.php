<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Footer;
use App\Policies\FooterPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FooterPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $footer = new Footer();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [Footer::class, $footer]),
                $ability
            );
        }
    }

    public function test_non_admin_roles_denied(): void
    {
        $policy = new FooterPolicy();
        $footer = new Footer();

        foreach ([UserRole::Editor, UserRole::Viewer] as $role) {
            $user = $this->userOfRole($role);

            $this->assertFalse($policy->viewAny($user), $role->value);
            $this->assertFalse($policy->view($user, $footer), $role->value);
            $this->assertFalse($policy->create($user), $role->value);
            $this->assertFalse($policy->update($user, $footer), $role->value);
            $this->assertFalse($policy->delete($user, $footer), $role->value);
            $this->assertFalse($policy->deleteAny($user), $role->value);
        }
    }
}
