<?php

namespace Tests\Unit\Blocks\Renderers;

use App\Blocks\Renderers\RelatedThematicRenderer;
use App\Enums\CategoryStatus;
use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StubHasBlockSections;
use Tests\TestCase;

class RelatedThematicRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_and_version(): void
    {
        $this->assertSame('related-thematic', RelatedThematicRenderer::key());
        $this->assertSame('1', RelatedThematicRenderer::version());
    }

    public function test_returns_empty_for_unsupported_model(): void
    {
        $html = app(RelatedThematicRenderer::class)
            ->render([], new StubHasBlockSections, 0);

        $this->assertSame('', $html);
    }

    public function test_post_without_category_ids_uses_own_categories_and_excludes_self(): void
    {
        $category = $this->postCategory('Tech', 'tech');

        $current = $this->publishedPost('Current', 'current');
        $related = $this->publishedPost('Related', 'related');
        $other = $this->publishedPost('Other', 'other');

        $current->categories()->attach($category->id);
        $related->categories()->attach($category->id);
        // $other без категории — не должен попасть

        $html = app(RelatedThematicRenderer::class)->render([], $current, 0);

        $this->assertStringContainsString('Related', $html);
        $this->assertStringNotContainsString('Current', $html);
        $this->assertStringNotContainsString('Other', $html);
    }

    public function test_post_with_category_ids_ignores_own_categories(): void
    {
        $ownCat = $this->postCategory('Own', 'own');
        $overrideCat = $this->postCategory('Override', 'override');

        $current = $this->publishedPost('Current', 'current');
        $current->categories()->attach($ownCat->id);

        $matchesOverride = $this->publishedPost('Matches', 'matches');
        $matchesOverride->categories()->attach($overrideCat->id);

        $matchesOwn = $this->publishedPost('MatchesOwn', 'matches-own');
        $matchesOwn->categories()->attach($ownCat->id);

        $html = app(RelatedThematicRenderer::class)
            ->render(['categoryIds' => [$overrideCat->id]], $current, 0);

        $this->assertStringContainsString('Matches', $html);
        $this->assertStringNotContainsString('MatchesOwn', $html);
    }

    public function test_post_without_categories_and_without_override_returns_empty(): void
    {
        $current = $this->publishedPost('Current', 'current');

        $html = app(RelatedThematicRenderer::class)->render([], $current, 0);

        $this->assertSame('', $html);
    }

    public function test_limit_is_clamped(): void
    {
        $category = $this->postCategory('Tech', 'tech');
        $current = $this->publishedPost('Current', 'current');
        $current->categories()->attach($category->id);

        for ($i = 0; $i < 3; $i++) {
            $post = $this->publishedPost('Post '.$i, 'post-'.$i);
            $post->categories()->attach($category->id);
        }

        $html = app(RelatedThematicRenderer::class)
            ->render(['limit' => 0], $current, 0);

        // limit 0 → кламппится к 1 → одна карточка присутствует
        $this->assertMatchesRegularExpression('/Post [012]/', $html);
    }

    public function test_button_url_uses_first_category_for_post(): void
    {
        $catA = $this->postCategory('Alpha', 'alpha');
        $catB = $this->postCategory('Beta', 'beta');

        $current = $this->publishedPost('Current', 'current');
        // orderBy('name') → alpha раньше beta
        $current->categories()->attach([$catA->id, $catB->id]);

        $related = $this->publishedPost('Related', 'related');
        $related->categories()->attach($catA->id);

        $html = app(RelatedThematicRenderer::class)->render([], $current, 0);

        $this->assertStringContainsString('newsCategory=alpha', $html);
    }

    public function test_project_variant_uses_project_query_and_projects_prefix(): void
    {
        $category = $this->projectCategory('Design', 'design');

        $current = $this->publishedProject('Current', 'current');
        $related = $this->publishedProject('Related', 'related');

        $current->categories()->attach($category->id);
        $related->categories()->attach($category->id);

        $html = app(RelatedThematicRenderer::class)->render([], $current, 0);

        $this->assertStringContainsString('Related', $html);
        $this->assertStringNotContainsString('Current', $html);
        $this->assertStringContainsString('projectsCategory=design', $html);
    }

    private function postCategory(string $name, string $slug): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => $slug,
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Posts->value,
        ]);
    }

    private function projectCategory(string $name, string $slug): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => $slug,
            'status' => CategoryStatus::Published->value,
            'type' => CategoryType::Projects->value,
        ]);
    }

    private function publishedPost(string $title, string $slug): Post
    {
        return Post::create([
            'title' => $title,
            'description' => 'Desc',
            'slug' => $slug,
            'status' => PostStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);
    }

    private function publishedProject(string $title, string $slug): Project
    {
        return Project::create([
            'title' => $title,
            'description' => 'Desc',
            'slug' => $slug,
            'status' => ProjectStatus::Published->value,
            'published_at' => now()->subDay(),
        ]);
    }
}
