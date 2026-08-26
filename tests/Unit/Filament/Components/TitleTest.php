<?php

namespace Tests\Unit\Filament\Components;

use App\Filament\Components\Title;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use ReflectionClass;
use Tests\TestCase;

class TitleTest extends TestCase
{
    public function test_key_and_block_name(): void
    {
        $this->assertSame('title', Title::key());
        $this->assertSame('title', Title::block()->getName());
    }

    public function test_default_block_carries_no_heading(): void
    {
        $this->assertSame([
            [
                'type' => 'title',
                'data' => [
                    'type' => 'h2',
                    'position' => 'center',
                ],
            ],
        ], Title::getDefaultBlock());
    }

    public function test_syncable_key_is_the_first_empty_title_block(): void
    {
        $key = $this->findSyncableBlockKey([
            'uuid-1' => ['type' => 'text-full', 'data' => ['text' => 'Hi']],
            'uuid-2' => ['type' => 'title', 'data' => ['type' => 'h2']],
            'uuid-3' => ['type' => 'title', 'data' => ['type' => 'h2']],
        ], null);

        $this->assertSame('uuid-2', $key);
    }

    public function test_syncable_key_is_returned_when_heading_still_mirrors_the_old_title(): void
    {
        $key = $this->findSyncableBlockKey([
            'uuid-1' => ['type' => 'title', 'data' => ['title' => 'Старый заголовок']],
        ], 'Старый заголовок');

        $this->assertSame('uuid-1', $key);
    }

    public function test_manually_edited_heading_is_not_syncable(): void
    {
        $key = $this->findSyncableBlockKey([
            'uuid-1' => ['type' => 'title', 'data' => ['title' => 'Свой заголовок']],
        ], 'Старый заголовок');

        $this->assertNull($key);
    }

    public function test_no_syncable_key_without_title_block(): void
    {
        $this->assertNull($this->findSyncableBlockKey([
            'uuid-1' => ['type' => 'text-full', 'data' => ['text' => 'Hi']],
        ], null));

        $this->assertNull($this->findSyncableBlockKey(null, null));
    }

    public function test_count_blocks_walks_across_sections_and_nested_builders(): void
    {
        $count = $this->countBlocks([
            'title' => 'Запись',
            'top_section' => [
                'uuid-1' => ['type' => 'image-tittle-full-width', 'data' => ['title' => 'НОВОСТИ']],
            ],
            'main_section' => [
                'uuid-2' => ['type' => 'title', 'data' => ['title' => 'A', 'type' => 'h2']],
                'uuid-3' => [
                    'type' => 'tabs-block',
                    'data' => [
                        'tabs' => [
                            'tab-a' => [
                                'title' => 'Tab',
                                'tab' => [
                                    'uuid-4' => ['type' => 'title', 'data' => ['title' => 'B', 'type' => 'h3']],
                                    'uuid-5' => [
                                        'type' => 'modal-block',
                                        'data' => [
                                            'blocks' => [
                                                'uuid-6' => ['type' => 'title', 'data' => ['title' => 'C', 'type' => 'h2']],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame(3, $count);
    }

    public function test_default_heading_copies_the_record_title_for_the_only_block(): void
    {
        $livewire = $this->livewireFor(Post::class, [
            'title' => 'Новость про мост',
            'main_section' => [
                'uuid-1' => ['type' => 'title', 'data' => []],
            ],
        ]);

        $this->assertSame('Новость про мост', $this->defaultHeading($livewire));
    }

    public function test_default_heading_is_empty_when_another_title_block_exists(): void
    {
        $livewire = $this->livewireFor(Project::class, [
            'title' => 'Проект',
            'main_section' => [
                'uuid-1' => ['type' => 'title', 'data' => ['title' => 'Уже есть']],
                'uuid-2' => ['type' => 'title', 'data' => []],
            ],
        ]);

        $this->assertNull($this->defaultHeading($livewire));
    }

    public function test_default_heading_is_empty_without_record_title(): void
    {
        $livewire = $this->livewireFor(Post::class, [
            'title' => '',
            'main_section' => [
                'uuid-1' => ['type' => 'title', 'data' => []],
            ],
        ]);

        $this->assertNull($this->defaultHeading($livewire));
    }

    public function test_default_heading_is_empty_for_other_models(): void
    {
        $livewire = $this->livewireFor(Page::class, [
            'title' => 'Страница',
            'main_section' => [
                'uuid-1' => ['type' => 'title', 'data' => []],
            ],
        ]);

        $this->assertNull($this->defaultHeading($livewire));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function livewireFor(string $modelClass, array $data): object
    {
        return new class($modelClass, $data)
        {
            public function __construct(public string $modelClass, public ?array $data) {}

            public function getRecord(): ?object
            {
                return new $this->modelClass;
            }
        };
    }

    private function defaultHeading(object $livewire): ?string
    {
        return $this->callPrivate('defaultHeading', $livewire);
    }

    private function countBlocks(mixed $state): int
    {
        return $this->callPrivate('countBlocks', $state);
    }

    private function findSyncableBlockKey(mixed $items, ?string $old): string|int|null
    {
        return $this->callPrivate('findSyncableBlockKey', $items, $old);
    }

    private function callPrivate(string $name, mixed ...$arguments): mixed
    {
        $method = (new ReflectionClass(Title::class))->getMethod($name);
        $method->setAccessible(true);

        return $method->invoke(null, ...$arguments);
    }
}
