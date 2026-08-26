<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InlineSvgHelperTest extends TestCase
{
    public function test_returns_empty_string_for_null_or_empty(): void
    {
        $this->assertSame('', inline_svg(null));
        $this->assertSame('', inline_svg(''));
    }

    public function test_never_fetches_absolute_urls(): void
    {
        $this->assertSame('', inline_svg('http://niipigrad.ru/storage/images/footer/logo-vk.svg'));
        $this->assertSame('', inline_svg('https://niipigrad.ru/storage/images/footer/logo-vk.svg'));
    }

    public function test_reads_from_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/footer/logo-vk.svg', '<svg>vk</svg>');

        $this->assertSame('<svg>vk</svg>', inline_svg('images/footer/logo-vk.svg'));
        $this->assertSame('<svg>vk</svg>', inline_svg('/images/footer/logo-vk.svg'));
    }

    public function test_resources_win_over_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/dup.svg', '<svg>storage</svg>');

        $resourceFile = resource_path('images/dup.svg');
        file_put_contents($resourceFile, '<svg>resources</svg>');

        try {
            $this->assertSame('<svg>resources</svg>', inline_svg('images/dup.svg'));
        } finally {
            @unlink($resourceFile);
        }
    }

    public function test_returns_empty_string_for_missing_file(): void
    {
        Storage::fake('public');

        $this->assertSame('', inline_svg('images/footer/nope.svg'));
    }

    public function test_rejects_traversal_outside_the_two_roots(): void
    {
        Storage::fake('public');

        $outside = Storage::disk('public')->path('../leak.svg');
        file_put_contents($outside, '<svg>leak</svg>');

        try {
            $this->assertSame('', inline_svg('../leak.svg'));
            $this->assertSame('', inline_svg('images/../../leak.svg'));
            $this->assertSame('', inline_svg('../.env'));
        } finally {
            @unlink($outside);
        }
    }

    public function test_rejects_paths_that_are_not_svg(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/photo.png', 'binary');

        $this->assertSame('', inline_svg('images/photo.png'));
    }
}
