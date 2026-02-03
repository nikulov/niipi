<?php

namespace App\Blocks;

use App\Blocks\Contracts\BlockRenderer;
use App\Blocks\Renderers\AccordionLightRenderer;
use App\Blocks\Renderers\AccordionRenderer;
use App\Blocks\Renderers\ButtonRenderer;
use App\Blocks\Renderers\CardsBlockWithButtonRenderer;
use App\Blocks\Renderers\CardsBlockWithImageTitleRenderer;
use App\Blocks\Renderers\CategoryListRenderer;
use App\Blocks\Renderers\FormRenderer;
use App\Blocks\Renderers\GalleryRenderer;
use App\Blocks\Renderers\ImageFullRenderer;
use App\Blocks\Renderers\ImageTextRenderer;
use App\Blocks\Renderers\ImageTittleFullWidthRenderer;
use App\Blocks\Renderers\InfoBlockWithAchievementsRenderer;
use App\Blocks\Renderers\InfoBlockWithButtonsRenderer;
use App\Blocks\Renderers\ModalBlockRenderer;
use App\Blocks\Renderers\NewsBlockRenderer;
use App\Blocks\Renderers\NewsFullRenderer;
use App\Blocks\Renderers\ProjectsBlockRenderer;
use App\Blocks\Renderers\ProjectsFullRenderer;
use App\Blocks\Renderers\SliderFullWidthRenderer;
use App\Blocks\Renderers\TabsBlockRenderer;
use App\Blocks\Renderers\TextFullRenderer;
use App\Blocks\Renderers\TitleRenderer;
use App\Blocks\Renderers\YandexMapRenderer;
use App\Filament\Components\CardsBlockWithImageTitle;


final class BlockRenderRegistry
{
    /** @return array<string,class-string<BlockRenderer>> */
    public static function map(): array
    {
        return [
            AccordionRenderer::key() => AccordionRenderer::class,
            AccordionLightRenderer::key() => AccordionLightRenderer::class,
            ButtonRenderer::key() => ButtonRenderer::class,
            CardsBlockWithButtonRenderer::key() => CardsBlockWithButtonRenderer::class,
            CardsBlockWithImageTitleRenderer::key() => CardsBlockWithImageTitleRenderer::class,
            CategoryListRenderer::key() => CategoryListRenderer::class,
            FormRenderer::key() => FormRenderer::class,
            GalleryRenderer::key() => GalleryRenderer::class,
            ImageFullRenderer::key() => ImageFullRenderer::class,
            ImageTextRenderer::key() => ImageTextRenderer::class,
            ImageTittleFullWidthRenderer::key() => ImageTittleFullWidthRenderer::class,
            InfoBlockWithAchievementsRenderer::key() => InfoBlockWithAchievementsRenderer::class,
            InfoBlockWithButtonsRenderer::key() => InfoBlockWithButtonsRenderer::class,
            ModalBlockRenderer::key() => ModalBlockRenderer::class,
            NewsBlockRenderer::key() => NewsBlockRenderer::class,
            NewsFullRenderer::key() => NewsFullRenderer::class,
            ProjectsBlockRenderer::key() => ProjectsBlockRenderer::class,
            ProjectsFullRenderer::key() => ProjectsFullRenderer::class,
            SliderFullWidthRenderer::key() => SliderFullWidthRenderer::class,
            TabsBlockRenderer::key() => TabsBlockRenderer::class,
            TextFullRenderer::key() => TextFullRenderer::class,
            TitleRenderer::key() => TitleRenderer::class,
            YandexMapRenderer::key() => YandexMapRenderer::class,
        ];
    }
    
    /** @return class-string<BlockRenderer>|null */
    public static function for(string $type): ?string
    {
        return self::map()[$type] ?? null;
    }
}
