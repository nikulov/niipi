<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\ModalBlockRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalBlockRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_modal_id_throws(): void
    {
        $renderer = new ModalBlockRenderer();

        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array { return []; }
            public function getRenderCacheId(): string { return 'page:1'; }
            public function getRenderUpdatedAtTimestamp(): int { return 0; }
        };

        $this->expectException(\InvalidArgumentException::class);

        $renderer->render([
            'modalId' => 'Bad Id',
            'modal' => [],
        ], $model, 0);
    }

    public function test_valid_modal_id_renders(): void
    {
        $renderer = new ModalBlockRenderer();

        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array { return []; }
            public function getRenderCacheId(): string { return 'page:1'; }
            public function getRenderUpdatedAtTimestamp(): int { return 0; }
        };

        $html = $renderer->render([
            'modalId' => 'modal-1',
            'title' => 'Modal Title',
            'modal' => [],
        ], $model, 0);

        $this->assertStringContainsString('#modal-1', $html);
        $this->assertStringContainsString('Modal Title', $html);
    }
}
