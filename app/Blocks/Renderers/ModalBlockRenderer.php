<?php

namespace App\Blocks\Renderers;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Contracts\HasBlockSections;
use App\Services\ContentRenderer;

final class ModalBlockRenderer implements BlockRenderer
{
    public static function key(): string { return 'modal-block'; }
    public static function version(): string { return '2'; }
    
    public function render(array $data, HasBlockSections $model, int $index): string
    {
        $blocks = $data['modal'] ?? [];
        
        $modalHtml = (string) app(ContentRenderer::class)->renderBlocks(
            blocks: $blocks,
            model: $model,
            section: null,
            indexOffset: ($index * 1000) + 500
        );
        
        $modalId = $this->resolveModalId($data);
        
        return view('components.sections.modal-block', [
            ...$data,
            'modalHtml' => $modalHtml,
            'modalId' => $modalId,
        ])->render();
    }
    
    private function resolveModalId(array $data): string
    {
        $modalId = trim((string) ($data['modalId'] ?? ''));
        
        if ($modalId === '' || preg_match('/^[a-z][a-z0-9_-]*$/', $modalId) !== 1) {
            throw new \InvalidArgumentException('Invalid modal_id for modal-block.');
        }
        
        return $modalId;
    }
}