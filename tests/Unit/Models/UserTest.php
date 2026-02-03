<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_hashed_on_set(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'secret',
        ]);

        $this->assertNotSame('secret', $user->password);
        $this->assertTrue(password_verify('secret', $user->password));
    }

    public function test_can_access_panel_returns_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->canAccessPanel(new Panel()));
    }
}
