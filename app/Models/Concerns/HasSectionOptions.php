<?php
namespace App\Models\Concerns;

trait HasSectionOptions
{
    protected function sectionOptionBlockTypes(): array
    {
        return [
            'bg-for-main-section',
        ];
    }
    
    public function isSectionOptionBlock(string $blockType): bool
    {
        return in_array($blockType, $this->sectionOptionBlockTypes(), true);
    }
    
    public function getSectionOption(
        string $section,
        string $blockType,
        string $key
    ): mixed {
        $blocks = $this->getBlocksForSection($section);
        
        foreach ($blocks as $block) {
            if (($block['type'] ?? null) !== $blockType) {
                continue;
            }
            
            return $block['data'][$key] ?? null;
        }
        
        return null;
    }
    
    public function getBgForMainSection(): ?string
    {
        return $this->getSectionOption(
            'main',
            'bg-for-main-section',
            'bgForMainSection'
        );
    }
}