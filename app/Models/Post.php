<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
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
}
