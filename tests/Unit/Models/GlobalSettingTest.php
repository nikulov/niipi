<?php

namespace Tests\Unit\Models;

use App\Models\GlobalSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GlobalSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_setting_returns_cached_record(): void
    {
        Cache::flush();

        $setting = GlobalSetting::getSetting();

        $this->assertNotNull($setting);
    }

    public function test_cache_is_invalidated_on_save(): void
    {
        Cache::flush();

        $setting = GlobalSetting::getSetting();
        $setting->update(['title' => 'Before']);

        $first = GlobalSetting::getSetting();
        $this->assertSame('Before', $first->title);

        $setting->update(['title' => 'After']);

        $second = GlobalSetting::getSetting();
        $this->assertSame('After', $second->title);
    }
}
