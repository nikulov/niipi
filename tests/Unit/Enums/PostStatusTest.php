<?php

namespace Tests\Unit\Enums;

use App\Enums\PostStatus;
use Tests\TestCase;

class PostStatusTest extends TestCase
{
    public function test_all_cases_expose_label_color_and_icon(): void
    {
        foreach (PostStatus::cases() as $case) {
            $this->assertIsString($case->getLabel());
            $this->assertNotSame('', $case->getColor());
            $this->assertIsString($case->getIcon());
        }
    }

    public function test_options_include_all_cases(): void
    {
        $this->assertSame(
            ['draft', 'published', 'archived'],
            array_keys(PostStatus::options())
        );
    }
}
