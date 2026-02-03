<?php

namespace Tests\Unit\Models;

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_relation(): void
    {
        $project = Project::create([
            'title' => 'Project',
            'description' => 'Project description',
            'slug' => 'project-1',
            'status' => ProjectStatus::Draft->value,
        ]);

        $category = Category::create([
            'name' => 'Projects',
            'slug' => 'projects',
            'status' => 'active',
            'type' => 'projects',
        ]);

        $project->categories()->attach($category->id);

        $this->assertSame(1, $project->categories()->count());
    }

    public function test_get_blocks_for_section_and_all(): void
    {
        $project = Project::create([
            'title' => 'Project',
            'description' => 'Project description',
            'slug' => 'project-2',
            'status' => ProjectStatus::Draft->value,
            'top_section' => [['type' => 'a']],
            'main_section' => [['type' => 'b']],
            'bottom_section' => [['type' => 'c']],
        ]);

        $this->assertSame([['type' => 'a']], $project->getBlocksForSection('top'));
        $this->assertSame([['type' => 'b']], $project->getBlocksForSection('main'));
        $this->assertSame([['type' => 'c']], $project->getBlocksForSection('bottom'));
        $this->assertSame([['type' => 'a'], ['type' => 'b'], ['type' => 'c']], $project->getBlocksForSection(null));
    }

    public function test_scope_published_filters_by_status_and_date(): void
    {
        $published = Project::create([
            'title' => 'Published',
            'description' => 'Desc',
            'slug' => 'published',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        Project::create([
            'title' => 'Draft',
            'description' => 'Desc',
            'slug' => 'draft',
            'status' => ProjectStatus::Draft->value,
            'published_at' => now()->subDay(),
        ]);

        Project::create([
            'title' => 'Future',
            'description' => 'Desc',
            'slug' => 'future',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->addDay(),
        ]);

        $results = Project::query()->published()->pluck('id')->all();

        $this->assertSame([$published->id], $results);
    }

    public function test_meta_uses_meta_title_fallback(): void
    {
        $project = new Project([
            'title' => 'Title',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Desc',
        ]);

        $meta = $project->meta();

        $this->assertSame('Meta Title', $meta['title']);
        $this->assertSame('Meta Desc', $meta['description']);
        $this->assertArrayHasKey('keywords', $meta);
    }
}
