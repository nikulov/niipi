<?php

namespace App\Models;

use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Model;


class Page extends Model
{
    protected $table = 'pages';
    
    protected $fillable = [
        'title',
        'slug',
        'status',
        'published_at',
        'top_section',
        'main_section',
        'bottom_section',
    
    ];
    
    protected $casts = [
        'status' => PageStatus::class,
        'top_section' => 'array',
        'main_section' => 'array',
        'bottom_section' => 'array',
        'published_at' => 'datetime',
    ];
    
    public function setSlugAttribute(?string $value): void
    {
        $this->attributes['slug'] = $value ? ltrim($value, '/') : null;
    }
    
}
