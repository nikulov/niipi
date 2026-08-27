<?php

namespace Tests\Unit\Filament\Resources;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Projects\Pages\CreateProject;
use ReflectionClass;
use Tests\TestCase;

class DefaultMainBlocksTest extends TestCase
{
    public function test_post_appends_category_list_share_button_and_related_thematic(): void
    {
        $state = $this->appendDefaultMainBlock(new CreatePost, [
            ['type' => 'title', 'data' => ['title' => 'Новость']],
        ]);

        $this->assertSame(
            ['title', 'category-list', 'share-button', 'related-thematic'],
            array_column($state, 'type'),
        );

        $this->assertSame('/news', $state[2]['data']['btnUrl']);
        $this->assertSame('Все новости', $state[2]['data']['btnLabel']);
        $this->assertSame('end', $state[2]['data']['btnPosition']);
        $this->assertSame('btn-secondary', $state[2]['data']['btnType']);
    }

    public function test_project_appends_category_list_and_share_button_without_related_thematic(): void
    {
        $state = $this->appendDefaultMainBlock(new CreateProject, [
            ['type' => 'title', 'data' => ['title' => 'Проект']],
        ]);

        $this->assertSame(
            ['title', 'category-list', 'share-button'],
            array_column($state, 'type'),
        );

        $this->assertSame('/projects', $state[2]['data']['btnUrl']);
        $this->assertSame('Все проекты', $state[2]['data']['btnLabel']);
        $this->assertSame('btn-secondary', $state[2]['data']['btnType']);
    }

    public function test_empty_main_section_gets_no_default_blocks(): void
    {
        $this->assertSame([], $this->appendDefaultMainBlock(new CreatePost, []));
        $this->assertSame([], $this->appendDefaultMainBlock(new CreateProject, []));
    }

    /**
     * @param  array<int, array<string, mixed>>  $state
     * @return array<int, array<string, mixed>>
     */
    private function appendDefaultMainBlock(object $page, array $state): array
    {
        $method = (new ReflectionClass($page))->getMethod('appendDefaultMainBlock');
        $method->setAccessible(true);

        return $method->invoke($page, $state);
    }
}
