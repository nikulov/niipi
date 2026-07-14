<?php

namespace Tests\Unit\Models\Concerns;

use App\Blocks\Contracts\HasBlockSections;
use App\Models\Concerns\HasSectionOptions;
use Tests\TestCase;

class HasSectionOptionsHolder implements HasBlockSections
{
    use HasSectionOptions;

    public function __construct(private readonly array $blocks = []) {}

    public function getBlocksForSection(?string $section): array
    {
        return $this->blocks;
    }

    public function getRenderCacheId(): string { return 'holder:1'; }

    public function getRenderUpdatedAtTimestamp(): int { return 0; }
}

class HasSectionOptionsTest extends TestCase
{
    public function test_is_section_option_block(): void
    {
        $holder = new HasSectionOptionsHolder();

        $this->assertTrue($holder->isSectionOptionBlock('bg-for-main-section'));
        $this->assertFalse($holder->isSectionOptionBlock('title'));
        $this->assertFalse($holder->isSectionOptionBlock(''));
    }

    public function test_get_section_option_reads_from_first_matching_block(): void
    {
        $holder = new HasSectionOptionsHolder([
            ['type' => 'title', 'data' => ['title' => 'ignored']],
            ['type' => 'bg-for-main-section', 'data' => ['bgForMainSection' => '/bg.png']],
            ['type' => 'bg-for-main-section', 'data' => ['bgForMainSection' => '/other.png']],
        ]);

        $this->assertSame(
            '/bg.png',
            $holder->getSectionOption('main', 'bg-for-main-section', 'bgForMainSection')
        );
    }

    public function test_get_section_option_returns_null_when_missing(): void
    {
        $holder = new HasSectionOptionsHolder([
            ['type' => 'title', 'data' => ['title' => 'x']],
        ]);

        $this->assertNull($holder->getSectionOption('main', 'bg-for-main-section', 'bgForMainSection'));
    }

    public function test_get_bg_for_main_section_shortcut(): void
    {
        $holder = new HasSectionOptionsHolder([
            ['type' => 'bg-for-main-section', 'data' => ['bgForMainSection' => '/hero.jpg']],
        ]);

        $this->assertSame('/hero.jpg', $holder->getBgForMainSection());
    }
}
