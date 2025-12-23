<?php

namespace App\Models;

use App\Blocks\Contracts\HasBlockSections;
use App\Contracts\HasMeta;
use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Model;


class Page extends Model implements HasBlockSections, HasMeta
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
    
    public function getBlocksForSection(?string $section): array
    {
        $map = [
            'top' => 'top_section',
            'main' => 'main_section',
            'bottom' => 'bottom_section',
        ];
        
        if ($section === null) {
            return array_merge(
                (array) ($this->top_section ?? []),
                (array) ($this->main_section ?? []),
                (array) ($this->bottom_section ?? [])
            );
        }
        
        if (!isset($map[$section])) {
            return [];
        }
        
        return (array) ($this->{$map[$section]} ?? []);
    }
    
    public function getRenderCacheId(): string
    {
        return 'page:' . $this->getKey();
    }
    
    public function getRenderUpdatedAtTimestamp(): int
    {
        return optional($this->updated_at)->timestamp ?? 0;
    }
    
    public function meta(): array
    {
        return [];
    }
    
}
