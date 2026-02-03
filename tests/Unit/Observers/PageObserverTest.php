<?php

namespace Tests\Unit\Observers;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PageObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_at_is_set_on_publish(): void
    {
        Carbon::setTestNow('2026-02-03 12:00:00');

        $page = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'status' => PageStatus::Published,
            'published_at' => null,
        ]);

        $this->assertNotNull($page->published_at);
        $this->assertSame('2026-02-03 12:00:00', $page->published_at->format('Y-m-d H:i:s'));
    }
}
