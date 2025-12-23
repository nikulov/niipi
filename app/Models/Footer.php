<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Footer extends Model
{
    protected $table = 'footer';
    
    protected $fillable = [
        'contact_data',
        'social_data',
    ];
    
    protected $casts = [
        'contact_data' => 'string',
        'social_data' => 'array',
    ];
    
    public static function cacheKey(): string
    {
        return 'footer.data';
    }
    
    public static function cachedData(): ?array
    {
        return Cache::rememberForever(self::cacheKey(), function () {
            $footer = self::query()->latest('id')->first();
            
            if (! $footer) {
                return null;
            }
            
            return [
                'contactData' => $footer->contact_data,
                'socialData' => $footer->social_data ?? [],
            ];
        });
    }
    
    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(self::cacheKey());
        
        static::saved($forget);
        static::deleted($forget);
    }
}
