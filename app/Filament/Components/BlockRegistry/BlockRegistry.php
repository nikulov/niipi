<?php

namespace App\Filament\Components\BlockRegistry;

use App\Filament\Components\Accordion;
use App\Filament\Components\AccordionLight;
use App\Filament\Components\BgForMainSection;
use App\Filament\Components\CardsBlockWithButton;
use App\Filament\Components\CategoryList;
use App\Filament\Components\Gallery;
use App\Filament\Components\ImageFull;
use App\Filament\Components\ImageText;
use App\Filament\Components\ImageTittleFullWidth;
use App\Filament\Components\InfoBlockWithAchievements;
use App\Filament\Components\InfoBlockWithButtons;
use App\Filament\Components\NewsBlock;
use App\Filament\Components\NewsFull;
use App\Filament\Components\ProjectsBlock;
use App\Filament\Components\ProjectsFull;
use App\Filament\Components\SliderFullWidth;
use App\Filament\Components\TextFull;
use App\Filament\Components\Title;
use Filament\Forms\Components\Builder\Block;

final class BlockRegistry
{
    /** @return Block[] */
    public static function all(): array
    {
        return [
            Accordion::block(),
            AccordionLight::block(),
            BgForMainSection::block(),
            CardsBlockWithButton::block(),
            CategoryList::block(),
            Gallery::block(),
            ImageFull::block(),
            ImageText::block(),
            ImageTittleFullWidth::block(),
            InfoBlockWithAchievements::block(),
            InfoBlockWithButtons::block(),
            NewsBlock::block(),
            NewsFull::block(),
            ProjectsBlock::block(),
            ProjectsFull::block(),
            SliderFullWidth::block(),
            TextFull::block(),
            Title::block(),
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
            BgForMainSection::block(),
            CardsBlockWithButton::block(),
            CategoryList::block(),
            Gallery::block(),
            ImageFull::block(),
            ImageText::block(),
            InfoBlockWithAchievements::block(),
            InfoBlockWithButtons::block(),
            NewsBlock::block(),
            NewsFull::block(),
            ProjectsBlock::block(),
            ProjectsFull::block(),
            TextFull::block(),
            Title::block(),
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
            BgForMainSection::key() => BgForMainSection::view(),
            CardsBlockWithButton::key() => CardsBlockWithButton::view(),
            CategoryList::key() => CategoryList::view(),
            Gallery::key() => Gallery::view(),
            ImageFull::key() => ImageFull::view(),
            ImageText::key() => ImageText::view(),
            ImageTittleFullWidth::key() => ImageTittleFullWidth::view(),
            InfoBlockWithAchievements::key() => InfoBlockWithAchievements::view(),
            InfoBlockWithButtons::key() => InfoBlockWithButtons::view(),
            NewsBlock::key() => NewsBlock::view(),
            NewsFull::key() => NewsFull::view(),
            ProjectsBlock::key() => ProjectsBlock::view(),
            SliderFullWidth::key() => SliderFullWidth::view(),
            TextFull::key() => TextFull::view(),
            Title::key() => Title::view(),
        
        
        ];
    }
}