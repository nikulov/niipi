<?php

namespace Tests\Unit\Enums;

use App\Enums\FormSubmissionStatus;
use Tests\TestCase;

class FormSubmissionStatusTest extends TestCase
{
    public function test_all_cases_expose_label_and_color(): void
    {
        foreach (FormSubmissionStatus::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertNotSame('', $case->getColor());
        }
    }

    public function test_options_include_all_cases(): void
    {
        $opts = FormSubmissionStatus::options();

        $this->assertSame(
            ['new', 'processing', 'sent', 'failed'],
            array_keys($opts)
        );
    }
}
