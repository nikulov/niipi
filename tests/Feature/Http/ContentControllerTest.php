<?php

namespace Tests\Feature\Http;

use App\Enums\PageStatus;
use App\Enums\PostStatus;
use App\Enums\ProjectStatus;
use App\Models\Footer;
use App\Models\GlobalSetting;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function seedLayoutDependencies(): void
    {
        GlobalSetting::create(['title' => 'Site']);
        Menu::create(['top_items' => [], 'footer_items' => []]);
        Footer::create(['contact_data' => '', 'social_data' => []]);
    }

    public function test_home_page_renders(): void
    {
        $this->seedLayoutDependencies();

        Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'status' => PageStatus::Published,
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/')
            ->assertStatus(200);
    }

    public function test_post_page_renders(): void
    {
        $this->seedLayoutDependencies();

        $post = Post::create([
            'title' => 'Post',
            'description' => 'Desc',
            'slug' => 'post-1',
            'status' => PostStatus::Published,
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/news/' . $post->slug)
            ->assertStatus(200);
    }

    public function test_project_page_renders(): void
    {
        $this->seedLayoutDependencies();

        $project = Project::create([
            'title' => 'Project',
            'description' => 'Desc',
            'slug' => 'project-1',
            'status' => ProjectStatus::Published,
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/projects/' . $project->slug)
            ->assertStatus(200);
    }
}
