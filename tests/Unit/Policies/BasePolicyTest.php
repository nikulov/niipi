<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\BasePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasePolicyDouble extends BasePolicy
{
    public function publicIsEditor(User $user): bool
    {
        return $this->isEditor($user);
    }

    public function publicIsViewer(User $user): bool
    {
        return $this->isViewer($user);
    }

    public function publicIsEditorOrViewer(User $user): bool
    {
        return $this->isEditorOrViewer($user);
    }
}

class BasePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_before_bypasses_admin(): void
    {
        $policy = new BasePolicyDouble();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->assertTrue($policy->before($admin, 'anything'));
    }

    public function test_before_returns_null_for_non_admin(): void
    {
        $policy = new BasePolicyDouble();

        foreach ([UserRole::Editor, UserRole::Viewer] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertNull($policy->before($user, 'anything'), $role->value);
        }
    }

    public function test_role_helpers(): void
    {
        $policy = new BasePolicyDouble();

        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->assertTrue($policy->publicIsEditor($editor));
        $this->assertFalse($policy->publicIsEditor($viewer));
        $this->assertFalse($policy->publicIsEditor($admin));

        $this->assertTrue($policy->publicIsViewer($viewer));
        $this->assertFalse($policy->publicIsViewer($editor));
        $this->assertFalse($policy->publicIsViewer($admin));

        $this->assertTrue($policy->publicIsEditorOrViewer($editor));
        $this->assertTrue($policy->publicIsEditorOrViewer($viewer));
        $this->assertFalse($policy->publicIsEditorOrViewer($admin));
    }
}
