<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Contracts\HasBlockSections;
use App\Blocks\Renderers\ProjectsBlockRenderer;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsBlockRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_block_with_cards(): void
    {
        Project::create([
            'title' => 'Project A',
            'description' => 'Desc',
            'slug' => 'project-a',
            'thumbnail' => '/img.jpg',
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);

        $html = $this->renderBlock([]);

        $this->assertStringContainsString('Projects', $html);
        $this->assertStringContainsString('Project A', $html);
        $this->assertStringContainsString('All projects', $html);
    }

    public function test_pinned_projects_come_first_and_are_not_duplicated(): void
    {
        $newest = $this->project('Newest', 'newest', now()->subDay());
        $oldest = $this->project('Oldest', 'oldest', now()->subDays(3));
        $middle = $this->project('Middle', 'middle', now()->subDays(2));

        $html = $this->renderBlock([
            'limit' => 3,
            'projectIds' => [$oldest->id, $middle->id],
        ]);

        $this->assertSame(
            ['Oldest', 'Middle', 'Newest'],
            $this->renderedTitles($html)
        );
    }

    public function test_unpublished_pinned_project_is_replaced_by_autofill(): void
    {
        $draft = Project::create([
            'title' => 'Draft',
            'description' => 'Desc',
            'slug' => 'draft',
            'thumbnail' => '/img.jpg',
            'status' => ProjectStatus::Draft->value,
            'published_at' => now()->subDay(),
        ]);

        $pinned = $this->project('Pinned', 'pinned', now()->subDays(3));
        $this->project('Auto', 'auto', now()->subDay());

        $html = $this->renderBlock([
            'limit' => 2,
            'projectIds' => [$pinned->id, $draft->id],
        ]);

        $this->assertSame(['Pinned', 'Auto'], $this->renderedTitles($html));
    }

    public function test_pinned_projects_are_truncated_to_the_limit(): void
    {
        $first = $this->project('First', 'first', now()->subDay());
        $second = $this->project('Second', 'second', now()->subDays(2));
        $third = $this->project('Third', 'third', now()->subDays(3));

        $html = $this->renderBlock([
            'limit' => 2,
            'projectIds' => [$third->id, $second->id, $first->id],
        ]);

        $this->assertSame(['Third', 'Second'], $this->renderedTitles($html));
    }

    public function test_falls_back_to_four_projects_when_limit_is_missing(): void
    {
        foreach (range(1, 5) as $i) {
            $this->project('Project '.$i, 'project-'.$i, now()->subDays($i));
        }

        $html = $this->renderBlock([]);

        $this->assertCount(4, $this->renderedTitles($html));
    }

    private function renderBlock(array $data): string
    {
        $model = new class implements HasBlockSections
        {
            public function getBlocksForSection(?string $section): array
            {
                return [];
            }

            public function getRenderCacheId(): string
            {
                return 'page:1';
            }

            public function getRenderUpdatedAtTimestamp(): int
            {
                return 0;
            }
        };

        return app(ProjectsBlockRenderer::class)->render($data + [
            'bgImageUrl' => '/bg.jpg',
            'title' => 'Projects',
            'btnUrl' => '/projects',
            'btnLabel' => 'All projects',
        ], $model, 0);
    }

    /** @return string[] */
    private function renderedTitles(string $html): array
    {
        preg_match_all('~<p class="text-normal[^"]*"[^>]*>\s*(.+?)\s*</p>~s', $html, $matches);

        return $matches[1];
    }

    private function project(string $title, string $slug, $publishedAt): Project
    {
        return Project::create([
            'title' => $title,
            'description' => 'Desc',
            'slug' => $slug,
            'thumbnail' => '/img.jpg',
            'status' => ProjectStatus::Published->value,
            'published_at' => $publishedAt,
        ]);
    }
}
