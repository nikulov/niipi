<?php

namespace App\Models;

use App\Enums\MediaFileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    protected $fillable = [
        'path',
        'disk',
        'filename',
        'mime_type',
        'size',
        'type',
        'title',
        'alt',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'type' => MediaFileType::class,
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(MediaFileUsage::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        try {
            return Storage::disk($this->disk)->url($this->path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    protected static function booted(): void
    {
        static::deleted(function (): void {
            Cache::forget(Footer::cacheKey());
            Cache::forget('global_settings');
        });
    }
}
