<?php

namespace Tests\Unit\Observers;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PostObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_at_is_set_on_publish(): void
    {
        Carbon::setTestNow('2026-02-03 12:00:00');

        $post = Post::create([
            'title' => 'Post',
            'description' => 'Desc',
            'slug' => 'post',
            'status' => PostStatus::Published,
            'published_at' => null,
        ]);

        $this->assertNotNull($post->published_at);
        $this->assertSame('2026-02-03 12:00:00', $post->published_at->format('Y-m-d H:i:s'));
    }
}
