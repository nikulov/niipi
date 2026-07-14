<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicAssetHelperTest extends TestCase
{
    public function test_returns_null_for_null_or_empty(): void
    {
        $this->assertNull(public_asset(null));
        $this->assertNull(public_asset(''));
    }

    public function test_returns_absolute_urls_unchanged(): void
    {
        $this->assertSame('http://example.com/a.png', public_asset('http://example.com/a.png'));
        $this->assertSame('https://example.com/b.png', public_asset('https://example.com/b.png'));
        $this->assertSame('/local/c.png', public_asset('/local/c.png'));
    }

    public function test_relative_path_goes_through_public_disk(): void
    {
        Storage::fake('public');

        $expected = Storage::disk('public')->url('images/photo.jpg');

        $this->assertSame($expected, public_asset('images/photo.jpg'));
    }
}
