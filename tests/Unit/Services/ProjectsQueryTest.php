<?php

namespace Tests\Unit\Services;

use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use App\Services\ProjectsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ProjectsQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_returns_only_published_and_not_future(): void
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

        $service = new ProjectsQuery;
        $items = $service->list(10);

        $this->assertCount(1, $items);
        $this->assertSame($published->id, $items->first()->id);
    }

    public function test_list_applies_limit_and_excludes_given_id(): void
    {
        $newest = $this->publishedProject('Newest', 'newest', now()->subDay());
        $middle = $this->publishedProject('Middle', 'middle', now()->subDays(2));
        $this->publishedProject('Oldest', 'oldest', now()->subDays(3));

        $service = new ProjectsQuery;

        $limited = $service->list(2);
        $this->assertCount(2, $limited);
        $this->assertSame($newest->id, $limited->first()->id);

        $withoutNewest = $service->list(10, null, false, 'page', $newest->id);
        $this->assertCount(2, $withoutNewest);
        $this->assertSame($middle->id, $withoutNewest->first()->id);
    }

    public function test_list_filters_by_category_and_can_paginate(): void
    {
        $category = Category::create([
            'name' => 'Projects',
            'slug' => 'projects',
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Projects->value,
        ]);

        $projectA = Project::create([
            'title' => 'Project A',
            'description' => 'Desc',
            'slug' => 'project-a',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $projectB = Project::create([
            'title' => 'Project B',
            'description' => 'Desc',
            'slug' => 'project-b',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $projectA->categories()->attach($category->id);

        $service = new ProjectsQuery;

        $filtered = $service->list(10, [$category->id]);
        $this->assertCount(1, $filtered);
        $this->assertSame($projectA->id, $filtered->first()->id);

        $paginated = $service->list(1, null, true);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);
        $this->assertSame(1, $paginated->perPage());
        $this->assertSame(2, $paginated->total());
    }

    private function publishedProject(string $title, string $slug, $publishedAt): Project
    {
        return Project::create([
            'title' => $title,
            'description' => 'Desc',
            'slug' => $slug,
            'status' => ProjectStatus::Published->value,
            'published_at' => $publishedAt,
        ]);
    }
}
