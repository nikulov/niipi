<?php

namespace Tests\Unit\Livewire\Components;

use App\Livewire\Components\AbstractContentFull;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;
use Tests\TestCase;

class AbstractContentFullDouble extends AbstractContentFull
{
    protected function buildCategoriesQuery(?array $ids): Builder
    {
        throw new RuntimeException('DB not used in this test');
    }

    protected function getCacheKey(): string { return 'x.categories'; }

    protected function getCacheTags(): array { return ['x']; }

    protected function getCountColumn(): string { return 'items_count'; }

    protected function getPivotTable(): string { return 'category_x'; }

    protected function getPivotForeignKey(): string { return 'x_id'; }

    protected function queryString(): array { return []; }

    protected function getContentTable(): string { return 'items'; }

    protected function getContentPrimaryKey(): string { return 'id'; }

    protected function getStatusColumn(): string { return 'status'; }

    protected function getPublishedStatusValue(): string|int { return 'published'; }
}

class AbstractContentFullTest extends TestCase
{
    public function test_page_name_without_component_key(): void
    {
        $component = new AbstractContentFullDouble();

        $this->assertSame('page', $component->getPageName());
    }

    public function test_page_name_with_component_key_is_hashed(): void
    {
        $component = new AbstractContentFullDouble();
        $component->componentKey = 'news:sidebar';

        $this->assertSame('page_'.md5('news:sidebar'), $component->getPageName());
    }

    public function test_mount_normalizes_category_ids(): void
    {
        $component = new AbstractContentFullDouble();

        $component->mount(limit: 5, categoryIds: [7 => 12, 8 => 34], componentKey: 'k');

        $this->assertSame(5, $component->limit);
        $this->assertSame([12, 34], $component->categoryIds);
        $this->assertSame('k', $component->componentKey);
    }

    public function test_mount_accepts_null_category_ids(): void
    {
        $component = new AbstractContentFullDouble();

        $component->mount();

        $this->assertSame(10, $component->limit);
        $this->assertNull($component->categoryIds);
        $this->assertNull($component->componentKey);
    }
}
