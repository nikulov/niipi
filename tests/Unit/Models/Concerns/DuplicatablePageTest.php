<?php

namespace Tests\Unit\Models\Concerns;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicatablePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_appends_kopiya_suffix_and_resets_status(): void
    {
        $page = Page::create([
            'title' => 'Главная',
            'slug' => 'home',
            'status' => PageStatus::Published->value,
            'published_at' => now(),
        ]);

        $copy = $page->duplicate();

        $this->assertSame('Главная (копия)', $copy->title);
        $this->assertSame('home-copy', $copy->slug);
        $this->assertSame(PageStatus::Draft, $copy->status);
        $this->assertNull($copy->published_at);
    }

    public function test_repeated_duplicate_increments_counter(): void
    {
        $page = Page::create([
            'title' => 'X',
            'slug' => 'x-page',
            'status' => PageStatus::Draft->value,
        ]);

        $page->duplicate();
        $page->duplicate();
        $third = $page->duplicate();

        $this->assertSame('X (копия 3)', $third->title);
        $this->assertSame('x-page-copy-3', $third->slug);
    }
}
