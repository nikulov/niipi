<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use Illuminate\Database\Eloquent\Model;

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
    
    public function posts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->BelongsToMany(Post::class);
    }
}
