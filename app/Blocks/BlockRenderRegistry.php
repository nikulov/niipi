<?php

namespace App\Blocks;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Renderers\AccordionLightRenderer;
use App\Blocks\Renderers\AccordionRenderer;
use App\Blocks\Renderers\CardsBlockWithButtonRenderer;
use App\Blocks\Renderers\GalleryRenderer;
use App\Blocks\Renderers\ImageFullRenderer;
use App\Blocks\Renderers\ImageTittleFullWidthRenderer;
use App\Blocks\Renderers\InfoBlockWithAchievementsRenderer;
use App\Blocks\Renderers\InfoBlockWithButtonsRenderer;
use App\Blocks\Renderers\NewsBlockRenderer;
use App\Blocks\Renderers\SliderFullWidthRenderer;
use App\Blocks\Renderers\TextFullRenderer;
use App\Blocks\Renderers\TitleRenderer;


final class BlockRenderRegistry
{
    /** @return array<string,class-string<BlockRenderer>> */
    public static function map(): array
    {
        return [
            AccordionRenderer::key() => AccordionRenderer::class,
            AccordionLightRenderer::key() => AccordionLightRenderer::class,
            CardsBlockWithButtonRenderer::key() => CardsBlockWithButtonRenderer::class,
            GalleryRenderer::key() => GalleryRenderer::class,
            ImageFullRenderer::key() => ImageFullRenderer::class,
            ImageTittleFullWidthRenderer::key() => ImageTittleFullWidthRenderer::class,
            InfoBlockWithAchievementsRenderer::key() => InfoBlockWithAchievementsRenderer::class,
            InfoBlockWithButtonsRenderer::key() => InfoBlockWithButtonsRenderer::class,
            NewsBlockRenderer::key() => NewsBlockRenderer::class,
            SliderFullWidthRenderer::key() => SliderFullWidthRenderer::class,
            TextFullRenderer::key() => TextFullRenderer::class,
            TitleRenderer::key() => TitleRenderer::class,
            
            
        ];
    }
    
    /** @return class-string<BlockRenderer>|null */
    public static function for(string $type): ?string
    {
        return self::map()[$type] ?? null;
    }
}
