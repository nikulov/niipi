<?php

namespace Tests\Unit\Services;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\TitleRenderer;
use App\Services\ContentRenderer;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\Support\StubHasBlockSections;
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

    public function test_render_exception_is_logged_and_pipeline_continues(): void
    {
        $this->app->bind(TitleRenderer::class, fn () => new class implements BlockRenderer {
            public static function key(): string { return 'title'; }
            public static function version(): string { return '1'; }

            public function render(array $data, HasBlockSections $model, int $index): string
            {
                throw new RuntimeException('boom');
            }
        });

        Log::spy();

        $model = new StubHasBlockSections([
            ['type' => 'title', 'data' => []],
            ['type' => 'text-full', 'data' => ['textFull' => 'After boom']],
        ]);

        $html = (string) (new ContentRenderer())->renderSection($model, 'main');

        $this->assertStringContainsString('After boom', $html);
        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn ($message) => $message === 'Render failed');
    }
}
