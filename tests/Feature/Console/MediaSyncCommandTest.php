<?php

namespace Tests\Feature\Console;

use App\Enums\PageStatus;
use App\Models\MediaFile;
use App\Models\MediaFileUsage;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_it_indexes_existing_files_on_disk(): void
    {
        Storage::disk('public')->put('images/a.jpg', 'x');
        Storage::disk('public')->put('gallery/b.png', 'x');
        Storage::disk('public')->put('files/c.pdf', 'x');

        $this->artisan('media:sync')->assertSuccessful();

        $this->assertDatabaseHas('media_files', ['path' => 'images/a.jpg']);
        $this->assertDatabaseHas('media_files', ['path' => 'gallery/b.png']);
        $this->assertDatabaseHas('media_files', ['path' => 'files/c.pdf']);
    }

    public function test_it_skips_livewire_tmp_files(): void
    {
        Storage::disk('public')->put('livewire-tmp/foo.png', 'x');

        $this->artisan('media:sync')->assertSuccessful();

        $this->assertDatabaseMissing('media_files', ['path' => 'livewire-tmp/foo.png']);
    }

    public function test_it_skips_forms_directory(): void
    {
        Storage::disk('public')->put('forms/1/attachment.pdf', 'x');
        Storage::disk('public')->put('forms/user-mail-attachments/tpl.pdf', 'x');

        $this->artisan('media:sync')->assertSuccessful();

        $this->assertSame(0, MediaFile::where('path', 'like', 'forms/%')->count());
    }

    public function test_it_cleans_orphaned_records(): void
    {
        MediaFile::create([
            'path' => 'images/ghost.jpg',
            'disk' => 'public',
            'filename' => 'ghost.jpg',
            'size' => 0,
            'type' => 'image',
        ]);

        $this->artisan('media:sync')->assertSuccessful();

        $this->assertDatabaseMissing('media_files', ['path' => 'images/ghost.jpg']);
    }

    public function test_it_rebuilds_usages_for_tracked_models(): void
    {
        Storage::disk('public')->put('media/hero.png', 'x');

        $page = Page::create([
            'title' => 'X',
            'slug' => 'x',
            'status' => PageStatus::Draft->value,
            'top_section' => [['data' => ['url' => 'media/hero.png']]],
        ]);

        // Удалим все usages, чтобы убедиться, что команда пересобирает
        MediaFileUsage::query()->delete();

        $this->artisan('media:sync')->assertSuccessful();

        $this->assertDatabaseHas('media_file_usages', [
            'usable_type' => Page::class,
            'usable_id' => $page->id,
            'field' => 'top_section',
        ]);
    }

    public function test_usages_only_flag_skips_file_scan(): void
    {
        Storage::disk('public')->put('images/new.jpg', 'x');

        $this->artisan('media:sync', ['--usages-only' => true])->assertSuccessful();

        $this->assertDatabaseMissing('media_files', ['path' => 'images/new.jpg']);
    }
}
