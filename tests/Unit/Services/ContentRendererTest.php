<?php

namespace Tests\Unit\Services;

use App\Blocks\Contracts\HasBlockSections;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ContentRendererTest extends TestCase
{
    public function test_renders_known_blocks_and_logs_unknown(): void
    {
        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array
            {
                return [
                    ['type' => 'option-block', 'data' => []],
                    ['type' => 'unknown-block', 'data' => []],
                    ['type' => 'text-full', 'data' => ['textFull' => 'Hello']],
                    ['data' => ['textFull' => 'Missing type']],
                ];
            }

            public function getRenderCacheId(): string
            {
                return 'test:1';
            }

            public function getRenderUpdatedAtTimestamp(): int
            {
                return 123;
            }

            public function isSectionOptionBlock(string $type): bool
            {
                return $type === 'option-block';
            }
        };

        Log::spy();

        $renderer = new ContentRenderer();
        $html = (string) $renderer->renderSection($model, 'main');

        $this->assertStringContainsString('Hello', $html);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn ($message) => $message === 'Unknown block type');
    }
}
