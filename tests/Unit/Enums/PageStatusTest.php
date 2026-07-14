<?php

namespace Tests\Unit\Enums;

use App\Enums\PageStatus;
use Tests\TestCase;

class PageStatusTest extends TestCase
{
    public function test_all_cases_expose_label_color_and_icon(): void
    {
        foreach (PageStatus::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertNotSame('', $case->getColor());
            $this->assertIsString($case->getIcon());
        }
    }

    public function test_values_are_stable(): void
    {
        $this->assertSame('draft', PageStatus::Draft->value);
        $this->assertSame('published', PageStatus::Published->value);
        $this->assertSame('archived', PageStatus::Archived->value);
    }
}
