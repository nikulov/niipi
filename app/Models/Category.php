<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'type',
        'slug',
        'status',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];
    
    protected $casts = [
        'type' => CategoryType::class,
        'status' => CategoryStatus::class,
    ];
    
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'category_post');
    }
    
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'category_project');
    }
    
    public function scopePosts(Builder $query): Builder
    {
        return $query->where('type', CategoryType::Posts);
    }
    
    public function scopeProjects(Builder $query): Builder
    {
        return $query->where('type', CategoryType::Projects);
    }
    
    protected static function booted(): void
    {
        $flush = function (): void {
            foreach (self::cacheTags() as $tags) {
                cache()->tags($tags)->flush();
            }
        };
        
        static::saved($flush);
        static::deleted($flush);
    }
    
    private static function cacheTags(): array
    {
        return [
            ['news', 'categories'],
            ['projects', 'categories'],
        ];
    }
}
