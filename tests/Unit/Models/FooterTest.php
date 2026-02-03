<?php

namespace Tests\Unit\Models;

use App\Models\Footer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_data_returns_null_without_records(): void
    {
        $this->assertNull(Footer::cachedData());
    }

    public function test_cached_data_returns_latest_and_cache_is_invalidated(): void
    {
        Cache::flush();

        $first = Footer::create([
            'contact_data' => 'First',
            'social_data' => [['url' => 'https://example.com']],
        ]);

        $cached = Footer::cachedData();
        $this->assertSame('First', $cached['contactData']);

        $second = Footer::create([
            'contact_data' => 'Second',
            'social_data' => [['url' => 'https://example.org']],
        ]);

        $fresh = Footer::cachedData();
        $this->assertSame($second->contact_data, $fresh['contactData']);
    }
}
