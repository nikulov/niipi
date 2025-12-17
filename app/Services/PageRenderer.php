<?php

namespace App\Services;

use App\Blocks\BlockRenderRegistry;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class PageRenderer
{
    public function render(Page $page, int $ttl = 600): HtmlString
    {
        $cacheKey = $this->makeCacheKey($page, 'all');

        $html = Cache::remember($cacheKey, $ttl, function () use ($page) {
            return $this->renderBlocksForSection($page, null);
        });

        return new HtmlString($html);
    }
    
    
    // пока без кеша
//    public function renderSection(Page $page, string $section, int $ttl = 600): HtmlString
//    {
//        $cacheKey = $this->makeCacheKey($page, $section);
//
//        $html = Cache::remember($cacheKey, $ttl, function () use ($page, $section) {
//            return $this->renderBlocksForSection($page, $section);
//        });
//
//        return new HtmlString($html);
//    }
    
    public function renderSection(Page $page, string $section): HtmlString
    {
        $html = $this->renderBlocksForSection($page, $section);
        return new HtmlString($html);
    }
    
    private function renderBlocksForSection(Page $page, string $section): string
    {
        $blocks = $page->$section ?? [];
        
        $htmlBlocks = [];
        
        foreach ((array)$blocks as $index => $block) {
            
            $blockType = $block['type'] ?? null;
            $blockData = $block['data'] ?? [];
            
            if (!$blockType) {
                continue;
            }
            
            $rendererClass = BlockRenderRegistry::for($blockType);
            
            if (!$rendererClass) {
                Log::warning('Unknown block type', [
                    'section' => $section,
                    'type' => $blockType,
                    'page' => $page->id
                ]);
                continue;
            }
            
            try {
                $htmlBlocks[] = app($rendererClass)->render($blockData, $page, $index);
                
            } catch (\Throwable $e) {
                
                Log::error('Render failed', [
                    'section' => $section,
                    'type' => $blockType,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return implode("\n", array_filter($htmlBlocks));
    }
    
    /**
     * Ключ кэша: зависит от страницы, языка, версий блоков и секции.
     */
    private function makeCacheKey(Page $page, string $section): string
    {
        $updatedAt = optional($page->updated_at)->timestamp ?? 0;
        $locale = app()->getLocale();
        
        $versions = [];
        
        foreach ((array)($page->components ?? []) as $block) {
            $type = $block['type'] ?? '';
            $rendererClass = $type ? BlockRenderRegistry::for($type) : null;
            
            $versions[] = $rendererClass
                ? "{$type}:{$rendererClass::version()}"
                : "{$type}:0";
        }
        
        return 'page:render:' . $page->id
            . ':v' . $updatedAt
            . ':' . $locale
            . ':section:' . $section
            . ':' . md5(json_encode($versions));
    }
}