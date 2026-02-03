<?php

namespace Tests\Unit\Models;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_scopes_filter_by_type(): void
    {
        $postsCategory = Category::create([
            'name' => 'Posts',
            'slug' => 'posts',
            'status' => 'active',
            'type' => CategoryType::Posts->value,
        ]);

        $projectsCategory = Category::create([
            'name' => 'Projects',
            'slug' => 'projects',
            'status' => 'active',
            'type' => CategoryType::Projects->value,
        ]);

        $this->assertSame([$postsCategory->id], Category::query()->posts()->pluck('id')->all());
        $this->assertSame([$projectsCategory->id], Category::query()->projects()->pluck('id')->all());
    }

    public function test_items_count_attribute_uses_relation_count(): void
    {
        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'status' => 'active',
            'type' => CategoryType::Posts->value,
        ]);

        $post = Post::create([
            'title' => 'Post',
            'description' => 'Desc',
            'slug' => 'post-1',
            'status' => 'draft',
        ]);

        $category->posts()->attach($post->id);

        $fresh = Category::query()->withCount('posts')->findOrFail($category->id);

        $this->assertSame(1, $fresh->items_count);
    }

    public function test_posts_and_projects_relations(): void
    {
        $category = Category::create([
            'name' => 'Mixed',
            'slug' => 'mixed',
            'status' => 'active',
            'type' => CategoryType::Posts->value,
        ]);

        $post = Post::create([
            'title' => 'Post',
            'description' => 'Desc',
            'slug' => 'post-2',
            'status' => 'draft',
        ]);

        $project = Project::create([
            'title' => 'Project',
            'description' => 'Desc',
            'slug' => 'project-1',
            'status' => 'draft',
        ]);

        $category->posts()->attach($post->id);
        $category->projects()->attach($project->id);

        $this->assertSame(1, $category->posts()->count());
        $this->assertSame(1, $category->projects()->count());
    }
}
