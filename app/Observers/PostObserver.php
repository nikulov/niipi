<?php

namespace App\Observers;

use App\Enums\PostStatus;
use App\Models\Post;

class PostObserver
{
    public function saving(Post $post): void
    {
        if (
            $post->status === PostStatus::Published &&
            $post->published_at === null
        ) {
            $post->published_at = now();
        }
    }
}
