<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Builder\Block;

final class BlockRegistry
{
    /** @return Block[] */
    public static function all(): array
    {
        return [
            SliderFullWidth::block(),
            InfoBlockWithAchievements::block(),
            CardsBlockWithButton::block(),
            NewsBlock::block(),
            Accordion::block(),
            AccordionLight::block(),
            ImageTittleFullWidth::block(),
            ProjectsBlock::block(),
            InfoBlockWithButtons::block(),
        
        ];
    }
    
    public static function topSection(): array
    {
        return [
            SliderFullWidth::block(),
            ImageTittleFullWidth::block(),
        ];
    }
    
    public static function mainSection(): array
    {
        return [
            InfoBlockWithButtons::block(),
            InfoBlockWithAchievements::block(),
            CardsBlockWithButton::block(),
            Accordion::block(),
            AccordionLight::block(),
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
            SliderFullWidth::key() => SliderFullWidth::view(),
            InfoBlockWithAchievements::key() => InfoBlockWithAchievements::view(),
            CardsBlockWithButton::key() => CardsBlockWithButton::view(),
            NewsBlock::key() => NewsBlock::view(),
            Accordion::key() => Accordion::view(),
            AccordionLight::key() => AccordionLight::view(),
            ImageTittleFullWidth::key() => ImageTittleFullWidth::view(),
            ProjectsBlock::key() => ProjectsBlock::view(),
            InfoBlockWithButtons::key() => InfoBlockWithButtons::view(),
        
        
        ];
    }
}