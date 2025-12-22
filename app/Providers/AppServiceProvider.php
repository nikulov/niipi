<?php

namespace App\Providers;

use App\Models\GlobalSetting;
use App\Models\Post;
use App\Observers\PostObserver;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);
        
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);
        
        View::share('year', date('Y'));
        
        View::composer('*', function ($view) {
            $view->with('settings', GlobalSetting::getSetting());
        });
    }
}
