<?php

namespace Tests\Unit\Providers;

use App\Models\GlobalSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_shares_year_and_settings_with_views(): void
    {
        Cache::forget('global_settings');

        $setting = GlobalSetting::create([
            'title' => 'Site',
        ]);
        Cache::forget('global_settings');
        Cache::forever('global_settings', $setting);

        $view = view('emails.form-submission-admin', [
            'submission' => new \App\Models\FormSubmission(),
        ]);
        $view->render();
        $data = $view->getData();

        $this->assertTrue($data['settings']->is($setting));

        $this->assertSame(date('Y'), View::shared('year'));
    }
}
