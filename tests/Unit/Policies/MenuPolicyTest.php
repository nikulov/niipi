<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Menu;
use App\Policies\MenuPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class MenuPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $menu = new Menu();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [Menu::class, $menu]),
                $ability
            );
        }
    }

    public function test_non_admin_roles_denied(): void
    {
        $policy = new MenuPolicy();
        $menu = new Menu();

        foreach ([UserRole::Editor, UserRole::Viewer] as $role) {
            $user = $this->userOfRole($role);

            $this->assertFalse($policy->viewAny($user), $role->value);
            $this->assertFalse($policy->view($user, $menu), $role->value);
            $this->assertFalse($policy->create($user), $role->value);
            $this->assertFalse($policy->update($user, $menu), $role->value);
            $this->assertFalse($policy->delete($user, $menu), $role->value);
            $this->assertFalse($policy->deleteAny($user), $role->value);
        }
    }
}
