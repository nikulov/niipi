<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GlobalSetting extends Model
{
    protected $table = 'global_settings';
    
    protected $fillable = [
        'title',
        'description',
        'email',
        'favicon',
        'code_header',
        'code_body_top',
        'code_body_bottom',
        'code_footer',
    ];
    
    public static function getSetting()
    {
        return Cache::rememberForever('global_settings', fn () => self::first());
    }
    
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('global_settings'));
        static::deleted(fn () => Cache::forget('global_settings'));
    }
}
