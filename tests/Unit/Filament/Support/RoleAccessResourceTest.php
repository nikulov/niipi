<?php

namespace Tests\Unit\Filament\Support;

use App\Enums\UserRole;
use App\Filament\Support\RoleAccessResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class RoleAccessResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_denies_guest(): void
    {
        $resource = $this->resource();

        $this->assertFalse($resource::shouldRegisterNavigation());
        $this->assertFalse($resource::canViewAny());
    }

    public function test_default_allowed_roles_include_admin_only(): void
    {
        $resource = $this->resource();

        $this->actingAsRole(UserRole::Admin);
        $this->assertTrue($resource::shouldRegisterNavigation());
        $this->assertTrue($resource::canViewAny());

        $this->actingAsRole(UserRole::Editor);
        $this->assertFalse($resource::shouldRegisterNavigation());
        $this->assertFalse($resource::canViewAny());

        $this->actingAsRole(UserRole::Viewer);
        $this->assertFalse($resource::shouldRegisterNavigation());
        $this->assertFalse($resource::canViewAny());
    }

    public function test_allowed_roles_override_grants_access(): void
    {
        $resource = $this->resource([UserRole::Admin, UserRole::Editor]);

        $this->actingAsRole(UserRole::Editor);
        $this->assertTrue($resource::shouldRegisterNavigation());
        $this->assertTrue($resource::canViewAny());

        $this->actingAsRole(UserRole::Viewer);
        $this->assertFalse($resource::shouldRegisterNavigation());
        $this->assertFalse($resource::canViewAny());
    }

    public function test_wide_open_allowed_roles_include_all(): void
    {
        $resource = $this->resource([UserRole::Admin, UserRole::Editor, UserRole::Viewer]);

        foreach ([UserRole::Admin, UserRole::Editor, UserRole::Viewer] as $role) {
            $this->actingAsRole($role);
            $this->assertTrue($resource::shouldRegisterNavigation(), $role->value);
            $this->assertTrue($resource::canViewAny(), $role->value);
        }
    }

    private function actingAsRole(UserRole $role): User
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user, 'web');

        return $user;
    }

    /**
     * @param  array<int, UserRole>|null  $roles
     * @return class-string
     */
    private function resource(?array $roles = null): string
    {
        if ($roles === null) {
            return new class {
                use RoleAccessResource;
            }::class;
        }

        return match (true) {
            $roles === [UserRole::Admin, UserRole::Editor] => new class {
                use RoleAccessResource;

                protected static function allowedRoles(): array
                {
                    return [UserRole::Admin, UserRole::Editor];
                }
            }::class,
            $roles === [UserRole::Admin, UserRole::Editor, UserRole::Viewer] => new class {
                use RoleAccessResource;

                protected static function allowedRoles(): array
                {
                    return [UserRole::Admin, UserRole::Editor, UserRole::Viewer];
                }
            }::class,
            default => throw new InvalidArgumentException('Unsupported role set'),
        };
    }
}
