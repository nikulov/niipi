<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaFileUsage extends Model
{
    protected $fillable = ['media_file_id', 'usable_type', 'usable_id', 'field'];

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }
}
