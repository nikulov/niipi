<?php

namespace Tests\Unit\Enums;

use App\Enums\CategoryType;
use Tests\TestCase;

class CategoryTypeTest extends TestCase
{
    public function test_all_cases_expose_label_and_icon(): void
    {
        foreach (CategoryType::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertIsString($case->getIcon());
        }
    }

    public function test_options_include_all_cases(): void
    {
        $this->assertSame(
            ['posts', 'projects'],
            array_keys(CategoryType::options())
        );
    }
}
