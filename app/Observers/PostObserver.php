<?php

namespace App\Observers;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Support\Carbon;

class PostObserver
{
    public function saving(Post $post): void
    {
        if (
            $post->status === PostStatus::Published &&
            $post->published_at === null
        ) {
            $post->published_at = Carbon::now();
        }
    }
}
