<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function userOfRole(UserRole $role): User
    {
        return User::factory()->create(['role' => $role]);
    }
}
