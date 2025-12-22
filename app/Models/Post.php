<?php

namespace App\Models;

use App\Blocks\Contracts\HasBlockSections;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements HasBlockSections
{
    protected $fillable = [
        'title',
        'content',
        'category_id',
        'slug',
        'thumbnail',
        'status',
        'published_at',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];
    
    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
        'status' => PostStatus::class,
    ];
    
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
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
        return 'post:' . $this->getKey();
    }
    
    public function getRenderUpdatedAtTimestamp(): int
    {
        return optional($this->updated_at)->timestamp ?? 0;
    }
}
