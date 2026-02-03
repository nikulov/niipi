<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\FormRenderer;
use Livewire\Livewire;
use Tests\TestCase;

class FormRendererTest extends TestCase
{
    public function test_renders_via_livewire_mount(): void
    {
        Livewire::shouldReceive('mount')
            ->once()
            ->andReturn('<div>form</div>');

        $renderer = new FormRenderer();

        $model = new class implements HasBlockSections {
            public function getBlocksForSection(?string $section): array { return []; }
            public function getRenderCacheId(): string { return 'page:1'; }
            public function getRenderUpdatedAtTimestamp(): int { return 0; }
        };

        $html = $renderer->render([
            'form_id' => 123,
            'layout' => 'inline',
        ], $model, 1);

        $this->assertSame('<div>form</div>', $html);
    }
}
