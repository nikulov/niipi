<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\GlobalSetting;
use App\Policies\GlobalSettingPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GlobalSettingPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bypasses_all_abilities(): void
    {
        $admin = $this->userOfRole(UserRole::Admin);
        $setting = new GlobalSetting();

        foreach (['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny'] as $ability) {
            $this->assertTrue(
                Gate::forUser($admin)->allows($ability, [GlobalSetting::class, $setting]),
                $ability
            );
        }
    }

    public function test_non_admin_roles_denied(): void
    {
        $policy = new GlobalSettingPolicy();
        $setting = new GlobalSetting();

        foreach ([UserRole::Editor, UserRole::Viewer] as $role) {
            $user = $this->userOfRole($role);

            $this->assertFalse($policy->viewAny($user), $role->value);
            $this->assertFalse($policy->view($user, $setting), $role->value);
            $this->assertFalse($policy->create($user), $role->value);
            $this->assertFalse($policy->update($user, $setting), $role->value);
            $this->assertFalse($policy->delete($user, $setting), $role->value);
            $this->assertFalse($policy->deleteAny($user), $role->value);
        }
    }
}
