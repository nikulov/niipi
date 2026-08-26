<?php

namespace Tests\Feature\Http;

use App\Enums\PageStatus;
use App\Enums\PostStatus;
use App\Enums\ProjectStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_valid_xml_response(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', trim($response->getContent()));
        $this->assertStringContainsString('<urlset', $response->getContent());
    }

    public function test_only_published_entries_appear(): void
    {
        Page::create(['title' => 'Draft', 'slug' => 'draft-page', 'status' => PageStatus::Draft]);
        Page::create(['title' => 'Archived', 'slug' => 'archived-page', 'status' => PageStatus::Archived, 'published_at' => now()->subDay()]);
        Page::create(['title' => 'Future', 'slug' => 'future-page', 'status' => PageStatus::Published, 'published_at' => now()->addDay()]);
        Page::create(['title' => 'Live', 'slug' => 'live-page', 'status' => PageStatus::Published, 'published_at' => now()->subMinute()]);

        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString('/live-page', $body);
        $this->assertStringNotContainsString('/draft-page', $body);
        $this->assertStringNotContainsString('/archived-page', $body);
        $this->assertStringNotContainsString('/future-page', $body);
    }

    public function test_home_page_uses_root_url_not_slug(): void
    {
        Page::create(['title' => 'Home', 'slug' => 'home', 'status' => PageStatus::Published, 'published_at' => now()->subMinute()]);

        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString('<loc>'.route('home').'</loc>', $body);
        $this->assertStringNotContainsString('/home</loc>', $body);
    }

    public function test_post_and_project_urls_use_named_routes(): void
    {
        Post::create(['title' => 'N', 'description' => '', 'slug' => 'news-1', 'status' => PostStatus::Published, 'published_at' => now()->subMinute()]);
        Project::create(['title' => 'P', 'description' => '', 'slug' => 'project-1', 'status' => ProjectStatus::Published, 'published_at' => now()->subMinute()]);

        $body = $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString('<loc>'.route('news.show', 'news-1').'</loc>', $body);
        $this->assertStringContainsString('<loc>'.route('projects.show', 'project-1').'</loc>', $body);
    }

    public function test_creating_new_published_post_flushes_cache(): void
    {
        $this->assertStringNotContainsString('/news-2', $this->get('/sitemap.xml')->getContent());

        Post::create(['title' => 'N2', 'description' => '', 'slug' => 'news-2', 'status' => PostStatus::Published, 'published_at' => now()->subMinute()]);

        $this->assertStringContainsString('/news-2', $this->get('/sitemap.xml')->getContent());
    }

    public function test_deleting_post_flushes_cache(): void
    {
        $post = Post::create(['title' => 'N3', 'description' => '', 'slug' => 'news-3', 'status' => PostStatus::Published, 'published_at' => now()->subMinute()]);
        $this->assertStringContainsString('/news-3', $this->get('/sitemap.xml')->getContent());

        $post->delete();

        $this->assertStringNotContainsString('/news-3', $this->get('/sitemap.xml')->getContent());
    }

    public function test_archiving_page_flushes_cache(): void
    {
        $page = Page::create(['title' => 'X', 'slug' => 'to-archive', 'status' => PageStatus::Published, 'published_at' => now()->subMinute()]);
        $this->assertStringContainsString('/to-archive', $this->get('/sitemap.xml')->getContent());

        $page->update(['status' => PageStatus::Archived]);

        $this->assertStringNotContainsString('/to-archive', $this->get('/sitemap.xml')->getContent());
    }

    public function test_unpublishing_project_flushes_cache(): void
    {
        $project = Project::create(['title' => 'P2', 'description' => '', 'slug' => 'to-unpub', 'status' => ProjectStatus::Published, 'published_at' => now()->subMinute()]);
        $this->assertStringContainsString('/to-unpub', $this->get('/sitemap.xml')->getContent());

        $project->update(['status' => ProjectStatus::Draft]);

        $this->assertStringNotContainsString('/to-unpub', $this->get('/sitemap.xml')->getContent());
    }
}
