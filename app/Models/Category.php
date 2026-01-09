<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;

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
}
