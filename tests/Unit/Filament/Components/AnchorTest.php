<?php

namespace Tests\Unit\Filament\Components;

use App\Filament\Components\Anchor;
use ReflectionClass;
use Tests\TestCase;

class AnchorTest extends TestCase
{
    public function test_key_and_block_name(): void
    {
        $this->assertSame('anchor', Anchor::key());
        $this->assertSame('anchor', Anchor::block()->getName());
    }

    public function test_collect_anchor_counts_walks_flat_builder(): void
    {
        $counts = $this->collectAnchorCounts([
            'main_section' => [
                'uuid-1' => ['type' => 'anchor', 'data' => ['anchor' => 'foo']],
                'uuid-2' => ['type' => 'title', 'data' => ['title' => 'Hi']],
                'uuid-3' => ['type' => 'anchor', 'data' => ['anchor' => 'foo']],
                'uuid-4' => ['type' => 'anchor', 'data' => ['anchor' => 'bar']],
            ],
        ]);

        $this->assertSame(['foo' => 2, 'bar' => 1], $counts);
    }

    public function test_collect_anchor_counts_walks_across_page_sections(): void
    {
        $counts = $this->collectAnchorCounts([
            'top_section' => [
                'uuid-1' => ['type' => 'anchor', 'data' => ['anchor' => 'shared']],
            ],
            'main_section' => [
                'uuid-2' => ['type' => 'anchor', 'data' => ['anchor' => 'shared']],
            ],
            'bottom_section' => [
                'uuid-3' => ['type' => 'anchor', 'data' => ['anchor' => 'unique']],
            ],
        ]);

        $this->assertSame(['shared' => 2, 'unique' => 1], $counts);
    }

    public function test_collect_anchor_counts_walks_into_nested_tabs_and_modal(): void
    {
        $counts = $this->collectAnchorCounts([
            'main_section' => [
                'uuid-1' => ['type' => 'anchor', 'data' => ['anchor' => 'x']],
                'uuid-2' => [
                    'type' => 'tabs-block',
                    'data' => [
                        'tabs' => [
                            'tab-a' => [
                                'title' => 'A',
                                'tab' => [
                                    'uuid-3' => ['type' => 'anchor', 'data' => ['anchor' => 'x']],
                                ],
                            ],
                            'tab-b' => [
                                'title' => 'B',
                                'tab' => [
                                    'uuid-4' => [
                                        'type' => 'modal-block',
                                        'data' => [
                                            'blocks' => [
                                                'uuid-5' => ['type' => 'anchor', 'data' => ['anchor' => 'x']],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame(['x' => 3], $counts);
    }

    public function test_collect_anchor_counts_skips_blank_and_non_string(): void
    {
        $counts = $this->collectAnchorCounts([
            'main_section' => [
                'uuid-1' => ['type' => 'anchor', 'data' => ['anchor' => '']],
                'uuid-2' => ['type' => 'anchor', 'data' => ['anchor' => null]],
                'uuid-3' => ['type' => 'anchor', 'data' => []],
                'uuid-4' => ['type' => 'anchor', 'data' => ['anchor' => 'ok']],
            ],
        ]);

        $this->assertSame(['ok' => 1], $counts);
    }

    /**
     * @param  array<mixed, mixed>  $state
     * @return array<string, int>
     */
    private function collectAnchorCounts(array $state): array
    {
        $method = (new ReflectionClass(Anchor::class))->getMethod('collectAnchorCounts');
        $method->setAccessible(true);

        return $method->invoke(null, $state);
    }
}
