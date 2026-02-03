<?php

namespace Tests\Unit\Models;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_trimmed_on_set(): void
    {
        $page = Page::create([
            'title' => 'About',
            'slug' => '/about',
            'status' => PageStatus::Draft->value,
        ]);

        $this->assertSame('about', $page->slug);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'slug' => 'about']);
    }

    public function test_get_blocks_for_section_and_all(): void
    {
        $page = Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'status' => PageStatus::Draft->value,
            'top_section' => [['type' => 'a']],
            'main_section' => [['type' => 'b']],
            'bottom_section' => [['type' => 'c']],
        ]);

        $this->assertSame([['type' => 'a']], $page->getBlocksForSection('top'));
        $this->assertSame([['type' => 'b']], $page->getBlocksForSection('main'));
        $this->assertSame([['type' => 'c']], $page->getBlocksForSection('bottom'));

        $all = $page->getBlocksForSection(null);
        $this->assertSame([['type' => 'a'], ['type' => 'b'], ['type' => 'c']], $all);
    }

    public function test_scope_published_filters_by_status_and_date(): void
    {
        $published = Page::create([
            'title' => 'Published',
            'slug' => 'published',
            'status' => PageStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        Page::create([
            'title' => 'Draft',
            'slug' => 'draft',
            'status' => PageStatus::Draft->value,
            'published_at' => now()->subDay(),
        ]);

        Page::create([
            'title' => 'Future',
            'slug' => 'future',
            'status' => PageStatus::Published->value,
            'published_at' => now()->addDay(),
        ]);

        $results = Page::query()->published()->pluck('id')->all();

        $this->assertSame([$published->id], $results);
    }
}
