<?php

namespace Tests\Unit\View\Composers;

use App\Models\Footer;
use App\View\Composers\FooterComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FooterComposerTest extends TestCase
{
    use RefreshDatabase;

    public function test_shares_footer_from_cached_data(): void
    {
        Cache::forget(Footer::cacheKey());

        Footer::create([
            'contact_data' => '{"phone":"+7"}',
            'social_data' => ['tg' => 'https://t.me/x'],
        ]);

        $view = View::make('includes.footer');

        (new FooterComposer())->compose($view);

        $footer = $view->getData()['footer'] ?? null;

        $this->assertSame('{"phone":"+7"}', $footer['contactData']);
        $this->assertSame(['tg' => 'https://t.me/x'], $footer['socialData']);
    }

    public function test_shares_null_when_no_footer_row(): void
    {
        Cache::forget(Footer::cacheKey());

        $view = View::make('includes.footer');

        (new FooterComposer())->compose($view);

        $this->assertNull($view->getData()['footer']);
    }
}
