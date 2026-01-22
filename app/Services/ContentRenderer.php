<?php

namespace App\Services;

use App\Blocks\BlockRenderRegistry;
use App\Blocks\Contracts\HasBlockSections;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class ContentRenderer
{
    // пока без кеша
//    public function renderSection(HasBlockSections $model, string $section, int $ttl = 600): HtmlString
//    {
//        $cacheKey = $this->makeCacheKey($model, $section);
//
//        $html = Cache::remember($cacheKey, $ttl, function () use ($model, $section) {
//            return $this->renderBlocksForSection($model, $section);
//        });
//
//        return new HtmlString($html);
//    }
    
    public function renderSection(HasBlockSections $model, string $section): HtmlString
    {
        $html = $this->renderBlocksForSection($model, $section);
        return new HtmlString($html);
    }
    
    private function renderBlocksForSection(HasBlockSections $model, string $section): string
    {
        $blocks = $model->getBlocksForSection($section);
        
        $htmlBlocks = [];
        
        foreach ($blocks as $index => $block) {
            $blockType = $block['type'] ?? null;
            $blockData = (array) ($block['data'] ?? []);
            
            if (!$blockType) {
                continue;
            }
            
            if (method_exists($model, 'isSectionOptionBlock') && $model->isSectionOptionBlock($blockType)) {
                continue;
            }
            
            $rendererClass = BlockRenderRegistry::for($blockType);
            
            if (!$rendererClass) {
                Log::warning('Unknown block type', [
                    'section' => $section,
                    'type' => $blockType,
                    'model' => $model->getRenderCacheId(),
                ]);
                continue;
            }
            
            try {
                $htmlBlocks[] = app($rendererClass)->render($blockData, $model, (int) $index);
            } catch (\Throwable $e) {
                Log::error('Render failed', [
                    'section' => $section,
                    'type' => $blockType,
                    'model' => $model->getRenderCacheId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return implode("\n", array_filter($htmlBlocks));
    }
    
    /**
     * Ключ кэша: зависит от страницы, языка, версий блоков и секции.
     */
    private function makeCacheKey(HasBlockSections $model, string $section): string
    {
        $updatedAt = $model->getRenderUpdatedAtTimestamp();
        $locale = app()->getLocale();
        
        $blocks = $model->getBlocksForSection($section === 'all' ? null : $section);
        
        $versions = [];
        
        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $rendererClass = $type ? BlockRenderRegistry::for($type) : null;
            
            $versions[] = $rendererClass
                ? "{$type}:{$rendererClass::version()}"
                : "{$type}:0";
        }
        
        return 'content:render:'
            . $model->getRenderCacheId()
            . ':v' . $updatedAt
            . ':' . $locale
            . ':section:' . $section
            . ':' . md5(json_encode($versions));
    }
}