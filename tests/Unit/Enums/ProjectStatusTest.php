<?php

namespace Tests\Unit\Enums;

use App\Enums\ProjectStatus;
use Tests\TestCase;

class ProjectStatusTest extends TestCase
{
    public function test_all_cases_expose_label_color_and_icon(): void
    {
        foreach (ProjectStatus::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertNotSame('', $case->getColor());
            $this->assertIsString($case->getIcon());
        }
    }

    public function test_values_are_stable(): void
    {
        $this->assertSame('draft', ProjectStatus::Draft->value);
        $this->assertSame('published', ProjectStatus::Published->value);
        $this->assertSame('archived', ProjectStatus::Archived->value);
    }
}
