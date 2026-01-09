<?php

namespace App\Models;

use App\Blocks\Contracts\HasBlockSections;
use App\Contracts\HasMeta;
use App\Enums\PostStatus;
use App\Filament\Components\ImageTittleFullWidth;
use App\Models\Concerns\HasSectionOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model implements HasBlockSections, HasMeta
{
    use HasSectionOptions;
    protected $fillable = [
        'title',
        'description',
        'slug',
        'thumbnail',
        'status',
        'published_at',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'top_section',
        'main_section',
        'bottom_section',
    ];
    
    protected $casts = [
        'top_section' => 'array',
        'main_section' => 'array',
        'bottom_section' => 'array',
        'published_at' => 'datetime',
        'status' => PostStatus::class,
    ];
    
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_post');
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
                (array)($this->top_section ?? []),
                (array)($this->main_section ?? []),
                (array)($this->bottom_section ?? [])
            );
        }
        
        if (!isset($map[$section])) {
            return [];
        }
        
        return (array)($this->{$map[$section]} ?? []);
    }
    
    public function getRenderCacheId(): string
    {
        return 'post:' . $this->getKey();
    }
    
    public function getRenderUpdatedAtTimestamp(): int
    {
        return optional($this->updated_at)->timestamp ?? 0;
    }
    
    public static function getDefaultBlock(): array
    {
        return
            [
                [
                    'type' => ImageTittleFullWidth::key(),
                    'data' => [
                        'title' => "НОВОСТИ,\nМЕРОПРИЯТИЯ, СОБЫТИЯ",
                        'iconAlt' => 'icon',
                        'iconUrl' => 'images/Group104.svg',
                        'imageAlt' => 'image',
                        'imageUrl' => 'images/top-news.jpg',
                    ],
                ],
            ];
    }
    
    public function meta(): array
    {
        return [
            'title' => $this->meta_title ?? $this->title,
            'description' => $this->meta_description,
            'keywords' => $this->meta_keywords,
            ];
    }
}
