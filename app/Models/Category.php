<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];
    
    protected $casts = [
        'status' => CategoryStatus::class,
    ];
    
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
