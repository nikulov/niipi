<?php

namespace Tests\Unit\Enums;

use App\Enums\FormApplicantType;
use Tests\TestCase;

class FormApplicantTypeTest extends TestCase
{
    public function test_all_cases_expose_label_and_color(): void
    {
        foreach (FormApplicantType::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertSame('default', $case->getColor());
        }
    }

    public function test_options_include_all_cases(): void
    {
        $this->assertSame(
            ['person', 'company', 'all'],
            array_keys(FormApplicantType::options())
        );
    }
}
