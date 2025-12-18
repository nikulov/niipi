<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;

final class BlockRegistry
{
    /** @return Block[] */
    public static function all(): array
    {
        return [
            Accordion::block(),
            AccordionLight::block(),
            CardsBlockWithButton::block(),
            Gallery::block(),
            ImageTittleFullWidth::block(),
            InfoBlockWithAchievements::block(),
            InfoBlockWithButtons::block(),
            NewsBlock::block(),
            ProjectsBlock::block(),
            SliderFullWidth::block(),
        
        ];
    }
    
    public static function topSection(): array
    {
        return [
            ImageTittleFullWidth::block(),
            SliderFullWidth::block(),
        ];
    }
    
    public static function mainSection(): array
    {
        return [
            Accordion::block(),
            AccordionLight::block(),
            CardsBlockWithButton::block(),
            Gallery::block(),
            InfoBlockWithAchievements::block(),
            InfoBlockWithButtons::block(),
        ];
    }
    
    public static function bottomSection(): array
    {
        return [
            NewsBlock::block(),
            ProjectsBlock::block(),
        ];
    }
    
    /** key => view */
    public static function views(): array
    {
        return [
            Accordion::key() => Accordion::view(),
            AccordionLight::key() => AccordionLight::view(),
            CardsBlockWithButton::key() => CardsBlockWithButton::view(),
            Gallery::key() => Gallery::view(),
            ImageTittleFullWidth::key() => ImageTittleFullWidth::view(),
            InfoBlockWithAchievements::key() => InfoBlockWithAchievements::view(),
            InfoBlockWithButtons::key() => InfoBlockWithButtons::view(),
            NewsBlock::key() => NewsBlock::view(),
            ProjectsBlock::key() => ProjectsBlock::view(),
            SliderFullWidth::key() => SliderFullWidth::view(),
        
        
        ];
    }
}