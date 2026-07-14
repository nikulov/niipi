<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string}> */
    public static function indexRoutes(): array
    {
        return [
            'categories' => ['/admin/categories'],
            'footers' => ['/admin/footers'],
            'form-submissions' => ['/admin/form-submissions'],
            'forms' => ['/admin/forms'],
            'global-settings' => ['/admin/global-settings'],
            'menus' => ['/admin/menus'],
            'pages' => ['/admin/pages'],
            'posts' => ['/admin/posts'],
            'projects' => ['/admin/projects'],
            'users' => ['/admin/users'],
        ];
    }

    #[DataProvider('indexRoutes')]
    public function test_guest_is_redirected_to_login(string $url): void
    {
        $this->get($url)->assertRedirect('/admin/login');
    }

    #[DataProvider('indexRoutes')]
    public function test_admin_can_open_index(string $url): void
    {
        $admin = $this->userOfRole(UserRole::Admin);

        $this->actingAs($admin, 'web')->get($url)->assertOk();
    }
}
