<?php

namespace App\Filament\Widgets;

use App\Models\FormSubmission;
use Filament\Schemas\Components\Section;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubmissionsStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            
            Section::make(__('panel.submission'))
                ->schema([
                    
                    Stat::make(__('panel.new_today'),
                        
                        FormSubmission::query()
                            ->whereDate('created_at', today())
                            ->count()
                    )->color('primary'),
                    
                    Stat::make(__('panel.status_processing'),
                        
                        FormSubmission::query()
                            ->where('status', 'processing')
                            ->count()
                    )->color('warning'),
                    
                    
                    Stat::make(__('panel.status_failed'),
                        
                        FormSubmission::query()
                            ->where('status', 'failed')
                            ->count()
                    )->color('danger'),
                    
                ]),
        
        
        ];
    }
}
