<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_all_cases_expose_label_color_and_icon(): void
    {
        foreach (UserRole::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertNotSame('', $case->getColor());
            $this->assertIsString($case->getIcon());
        }
    }

    public function test_options_include_all_cases(): void
    {
        $opts = UserRole::options();

        $this->assertSame(
            ['admin', 'editor', 'viewer'],
            array_keys($opts)
        );
    }
}
