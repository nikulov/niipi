<?php

namespace App\Models\Concerns;

use App\Models\MediaFileUsage;
use App\Services\MediaUsageService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait TracksMediaUsage
{
    public static function bootTracksMediaUsage(): void
    {
        static::saved(function ($model): void {
            app(MediaUsageService::class)->syncForModel($model);
        });

        static::deleted(function ($model): void {
            app(MediaUsageService::class)->removeAllForModel($model);
        });
    }

    public function mediaUsages(): MorphMany
    {
        return $this->morphMany(MediaFileUsage::class, 'usable');
    }
}
