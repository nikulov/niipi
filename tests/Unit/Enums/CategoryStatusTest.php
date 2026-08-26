<?php

namespace Tests\Unit\Enums;

use App\Enums\CategoryStatus;
use Tests\TestCase;

class CategoryStatusTest extends TestCase
{
    public function test_all_cases_expose_label_and_color(): void
    {
        foreach (CategoryStatus::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertNotSame('', $case->getColor());
        }
    }

    public function test_values_are_stable(): void
    {
        $this->assertSame('draft', CategoryStatus::Draft->value);
        $this->assertSame('active', CategoryStatus::Published->value);
        $this->assertSame('archived', CategoryStatus::Archived->value);
    }
}
